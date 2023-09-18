<?php

namespace ZD\IR\Option;

use XF\Option\AbstractOption;

class CustomLinkValidation extends AbstractOption
{
    public static function verifyOption(&$value, \XF\Entity\Option $option)
    {
        if ($value !== '')
        {
            if (!\XF\Util\Php::isValidRegex($value))
            {
                $option->error(\XF::phrase('invalid_regular_expression'), $option->option_id);
                return false;
            }
        }

        return true;
    }
}