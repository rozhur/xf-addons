<?php

namespace ZD\IR\Util;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;
use ZD\IR\XF\Entity\User;

class CustomLink
{
    const ENTITIES = [
        [
            'entity' => 'XF:User',
            'route' => 'members'
        ],
        [
            'entity' => 'XF:Thread',
            'route' => 'threads'
        ],
        [
            'entity' => 'XF:Forum',
            'route' => 'forums',
            'linkKey' => 'node_name',
            'with' => 'Node'
        ],
        [
            'entity' => 'XF:Page',
            'route' => 'pages',
            'linkKey' => 'node_name',
            'with' => 'Node'
        ]
    ];

    public static function clearCustomLink($link)
    {
        $link = preg_replace('/[^\w\-_.~]|^[.\/\\\]+$/', '', $link);
        return mb_strtolower($link);
    }

    public static function verifyCustomLink(Entity $entity, &$link, $linkKey = 'zdir_custom_link')
    {
        $link = self::clearCustomLink($link);

        if ($link === '' || $entity->$linkKey === $link)
        {
            return true;
        }

        /** @var User $visitor */
        $visitor = \XF::visitor();
        if (!$entity->hasOption('admin_edit') || !$entity->getOption('admin_edit'))
        {
            if (!$visitor->canBypassCustomLinkLength())
            {
                $limit = $entity->app()->options()->zdirCustomLinkLength;
                $length = strlen($link);

                if ($length < $limit['min'])
                {
                    $entity->error(\XF::phrase('zdir_please_enter_custom_link_that_is_at_least_x_characters_long', [
                        'count' => $limit['min']
                    ]), $linkKey);
                    return false;
                }
                else if ($length > $limit['max'])
                {
                    $entity->error(\XF::phrase('zdir_please_enter_custom_link_that_is_at_most_x_characters_long', [
                        'count' => $limit['max']
                    ]), $linkKey);
                    return false;
                }
            }

            if (!$visitor->is_moderator && !$visitor->is_admin)
            {
                $disallowed = $entity->getOption('zdir_custom_link_disallowed');
                if ($disallowed)
                {
                    foreach ($disallowed as $value)
                    {
                        $value = trim($value);
                        if ($value === '')
                        {
                            continue;
                        }
                        if (stripos($link, $value) !== false)
                        {
                            $entity->error(\XF::phrase('zdir_please_enter_another_custom_link_disallowed_words'), $linkKey);
                            return false;
                        }
                    }
                }
            }

            if (!$visitor->canBypassCustomLinkRegex())
            {
                $regex = $entity->app()->options()->zdirCustomLinkRegex;
                if (!empty($regex) && !preg_match($regex, $link))
                {
                    $entity->error(\XF::phrase('zdir_please_enter_another_custom_link_required_format'), $linkKey);
                    return false;
                }
            }
        }

        return self::checkCustomLinkNotExists($entity, $link, $linkKey);
    }

    public static function checkCustomLinkNotExists(Entity $entity, $link, $linkKey = 'zdir_custom_link')
    {
        if (self::isCustomLinkExists($entity, $link))
        {
            $entity->error(\XF::phrase('zdir_custom_links_must_be_unique'), $linkKey);
            return false;
        }

        return true;
    }

    public static function isCustomLinkExists(Entity $entity, $link)
    {
        if (file_exists(\XF::getRootDirectory() . '/' . $link))
        {
            return true;
        }

        switch ($link)
        {
            case 'browserconfig.xml':
            case 'crossdomain.xml':
            case 'favicon.ico':
            case 'robots.txt':
                return true;
        }

        $match = $entity->app()['router.public']->routeToController($link);
        if ($match->getController())
        {
            return true;
        }

        return false;
    }

    public static function getFullStructure(Structure $structure)
    {
        $structure->columns['zdir_custom_link'] = ['type' => Entity::STR, 'maxLength' => 50, 'default' => ''];

        $structure->getters['custom_link_length_limits'] = true;
        $structure->getters['custom_link'] = false;

        $options = \XF::options();

        $structure->options['zdir_custom_link_disallowed'] = !empty($options->zdirDisallowedCustomLinks)
            ? preg_split('/\r?\n/', $options->zdirDisallowedCustomLinks)
            : [];

        return self::getCustomLinkIdentifierStructure($structure);
    }

    public static function getCustomLinkIdentifierStructure(Structure $structure)
    {
        $structure->getters['custom_link_identifier_key'] = false;
        $structure->getters['custom_link_identifier_value'] = false;

        return $structure;
    }
}