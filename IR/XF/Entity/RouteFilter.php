<?php

namespace ZD\IR\XF\Entity;

class RouteFilter extends XFCP_RouteFilter
{
    protected function verifyRoute(&$value)
    {
        $result = parent::verifyRoute($value);

        if (substr($value, -1) == '/')
        {
            $value = substr($value, 0, strlen($value) - 1);
        }

        return $result;
    }
}