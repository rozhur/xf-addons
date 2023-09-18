<?php 

namespace ZD\ESS\XF\Criteria;

use ZD\ESS\XF\Entity\Forum;

class Page extends XFCP_Page
{
    protected function _matchNodes(array $data, \XF\Entity\User $user)
    {
        if (parent::_matchNodes($data, $user))
        {
            if (empty($data['zdess_forum_status']) || $data['zdess_forum_status'] === 0)
            {
                return true;
            }

            $params = $this->pageState;
            if ($params['containerKey'])
            {
                [$type, $id] = explode('-', $params['containerKey'], 2);
                if ($type === 'node')
                {
                    /** @var Forum $forum */
                    $forum = $this->app->em()->find('XF:Forum', $id);
                    if ($forum && ($data['zdess_forum_status'] == 2 && $forum->zdess_forum_open || $data['zdess_forum_status'] == 1 && !$forum->zdess_forum_open))
                    {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
