<?php

namespace ZD\IR\XF\Str;

class MentionFormatter extends XFCP_MentionFormatter
{
    protected $routePrefix;

    public function __construct()
    {
        $routePrefix = \XF::app()->router('public')->buildLink('members');
        $routePrefix = substr($routePrefix, strlen(\XF::app()->request()->getBasePath()));

        $this->routePrefix = $routePrefix;
    }


    protected function getMentionMatchUsers(array $matches)
    {
        $db = \XF::db();
        $matchKeys = array_keys($matches);
        $whereParts = [];
        $matchParts = [];
        $usersByMatch = [];

        foreach ($matches AS $key => $match)
        {
            if (utf8_strlen($match[1][0]) > 50)
            {
                // longer than max username length
                continue;
            }

            $input = $match[1][0];
            if (preg_match('/^' . preg_quote($this->routePrefix) . '\d+?/', $input))
            {
                $input = substr($input, strlen($this->routePrefix));
                $input = $db->quote($db->escapeLike($input, '?%'));

                $sql = "user.user_id LIKE $input";
            }
            else
            {
                $input = $db->quote($db->escapeLike($input, '?%'));
                $sql = "user.username LIKE $input OR user.zdir_custom_link IS NOT NULL AND user.zdir_custom_link != '' AND user.zdir_custom_link LIKE $input";
            }

            $whereParts[] = $sql;
            $matchParts[] = 'IF(' . $sql . ', 1, 0) AS match_' . $key;
        }

        if (!$whereParts)
        {
            return [];
        }

        $finalSql = "
			SELECT user.user_id, user.username, user.zdir_custom_link,
				" . implode(', ', $matchParts) . "
			FROM xf_user AS user
			WHERE (" . implode(' OR ', $whereParts) . ")
			ORDER BY LENGTH(user.username) DESC
		";

        $userResults = $db->query($finalSql);

        while ($user = $userResults->fetch())
        {
            $userInfo = [
                'user_id' => $user['user_id'],
                'username' => $user['username'],
                'lower' => utf8_strtolower($user['username']),
                'zdir_custom_link' => $user['zdir_custom_link']
            ];

            foreach ($matchKeys AS $key)
            {
                if (!empty($user["match_$key"]))
                {
                    $usersByMatch[$key][$user['user_id']] = $userInfo;
                }
            }
        }

        return $usersByMatch;
    }

    protected function applyMentionUserMatches($message, array $matches, array $usersByMatch, \Closure $tagReplacement)
    {
        $this->mentionedUsers = [];

        if (!$usersByMatch)
        {
            return $message;
        }

        $newMessage = '';
        $lastOffset = 0;
        $mentionedUsers = [];
        $endMatch = $this->getTagEndPartialRegex(false);

        foreach ($matches AS $key => $match)
        {
            if ($match[0][1] > $lastOffset)
            {
                $newMessage .= substr($message, $lastOffset, $match[0][1] - $lastOffset);
            }
            else if ($lastOffset > $match[0][1])
            {
                continue;
            }

            $lastOffset = $match[0][1] + strlen($match[0][0]);

            $haveMatch = false;
            if (!empty($usersByMatch[$key]))
            {
                $testName = utf8_strtolower($match[1][0]);
                $testOffset = $match[1][1];

                foreach ($usersByMatch[$key] AS $userId => $user)
                {
                    // It's possible for the byte length to change between the lower and standard versions
                    // due to conversions like İ -> i (2 byte to 1). Therefore, we try to check whether either
                    // length matches the name.
                    $lowerLen = strlen($user['lower']);
                    $originalLen = strlen($user['username']);

                    if ($testName === $user['lower'])
                    {
                        $nameLen = $lowerLen;
                    }
                    else if (utf8_strtolower(substr($message, $testOffset, $lowerLen)) === $user['lower'])
                    {
                        $nameLen = $lowerLen;
                    }
                    else if (
                        $lowerLen !== $originalLen
                        && utf8_strtolower(substr($message, $testOffset, $originalLen)) === $user['lower']
                    )
                    {
                        $nameLen = $originalLen;
                    }
                    else
                    {
                        if (preg_match('/^' . preg_quote($this->routePrefix) . '\d+?/', $testName))
                        {
                            $user['username'] = $this->routePrefix . $userId;
                            $nameLen = strlen($user['username']);
                        }
                        else if ($user['zdir_custom_link'] !== '')
                        {
                            $user['username'] = $user['zdir_custom_link'];
                            $nameLen = strlen($user['zdir_custom_link']);
                        }
                        else
                        {
                            $nameLen = null;
                        }
                    }

                    $nextTestOffsetStart = $testOffset + ($nameLen ?: 0);

                    if (
                        $nameLen
                        && (
                            !isset($message[$nextTestOffsetStart])
                            || preg_match('#' . $endMatch . '#i', $message[$nextTestOffsetStart])
                        )
                    )
                    {
                        $mentionedUsers[$userId] = $user;
                        $newMessage .= $tagReplacement($user);
                        $haveMatch = true;
                        $lastOffset = $testOffset + strlen($user['username']);
                        break;
                    }
                }
            }

            if (!$haveMatch)
            {
                $newMessage .= $match[0][0];
            }
        }

        $newMessage .= substr($message, $lastOffset);

        $this->mentionedUsers = $mentionedUsers;

        return $newMessage;
    }
}