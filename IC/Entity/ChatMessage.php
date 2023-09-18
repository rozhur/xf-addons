<?php

namespace ZD\IC\Entity;

use XF\BbCode\RenderableContentInterface;
use XF\Entity\LinkableInterface;
use XF\Entity\QuotableInterface;
use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

class ChatMessage extends Entity implements LinkableInterface, QuotableInterface, RenderableContentInterface
{
    public function getContentUrl(bool $canonical = false, array $extraParams = [], $hash = null)
    {
        // TODO: Implement getContentUrl() method.
    }

    public function getContentPublicRoute()
    {
        // TODO: Implement getContentPublicRoute() method.
    }

    public function getContentTitle(string $context = '')
    {
        // TODO: Implement getContentTitle() method.
    }

    public function getQuoteWrapper($inner)
    {
        // TODO: Implement getQuoteWrapper() method.
    }

    public function getBbCodeRenderOptions($context, $type)
    {
        // TODO: Implement getBbCodeRenderOptions() method.
    }

    public static function getStructure(Structure $structure)
    {
        $structure->table = 'zdic_chat_message';
        $structure->shortName = 'ZD\IC:ChatMessage';
        $structure->primaryKey = 'chat_message_id';
        $structure->columns = [
            'chat_message_id' => ['type' => self::UINT, 'autoIncrement' => true, 'nullable' => true],
            'user_id' => ['type' => self::UINT, 'required' => true],
            'username' => ['type' => self::STR, 'maxLength' => 50,
                'required' => 'please_enter_valid_name'
            ],
            'recipient_ids' => ['type' => self::JSON_ARRAY, 'default' => []],
            'chat_message_date' => ['type' => self::UINT, 'required' => true, 'default' => \XF::$time],
            'message' => ['type' => self::STR, 'required' => 'please_enter_valid_message'],
            'ip_id' => ['type' => self::UINT, 'default' => 0],
            'message_state' => ['type' => self::STR, 'default' => 'visible',
                'allowedValues' => ['visible', 'moderated', 'deleted']
            ],
            'attach_count' => ['type' => self::UINT, 'max' => 65535, 'forced' => true, 'default' => 0],
            'warning_id' => ['type' => self::UINT, 'default' => 0],
            'warning_message' => ['type' => self::STR, 'default' => '', 'maxLength' => 255],
            'type_data' => ['type' => self::JSON_ARRAY, 'default' => []],
            'last_edit_date' => ['type' => self::UINT, 'default' => 0],
            'last_edit_user_id' => ['type' => self::UINT, 'default' => 0],
            'edit_count' => ['type' => self::UINT, 'forced' => true, 'default' => 0],
            'embed_metadata' => ['type' => self::JSON_ARRAY, 'nullable' => true, 'default' => null]
        ];

        return $structure;
    }
}