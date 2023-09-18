<?php

namespace ZD\IR\Entity;

/**
 * GETTERS
 * @property string $custom_link_identifier_key
 * @property string $custom_link_identifier_value
 * @property string $custom_link
 */
interface CustomLink
{
    public function getCustomLinkIdentifierKey();

    public function getCustomLinkIdentifierValue();

    public function getCustomLink();

    public function isCustomLinkChanged();
}