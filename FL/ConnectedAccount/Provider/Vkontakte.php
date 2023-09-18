<?php

namespace ZD\FL\ConnectedAccount\Provider;

use XF\ConnectedAccount\Provider\AbstractProvider;
use XF\Entity\ConnectedAccountProvider;

class Vkontakte extends AbstractProvider
{
    /**
     * @inheritDoc
     */
    public function getOAuthServiceName()
    {
        return 'Vkontakte';
    }

    /**
     * @return string
     */
    public function getProviderDataClass()
    {
        return 'ZD\\FL:ProviderData\\Vkontakte';
    }

    /**
     * @return array
     */
    public function getDefaultOptions()
    {
        return [
            'app_id' => '',
            'app_secret' => ''
        ];
    }

    public function getOAuthConfig(ConnectedAccountProvider $provider, $redirectUri = null)
    {
        return [
            'key' => $provider->options['app_id'],
            'secret' => $provider->options['app_secret'],
            'scopes' => [],
            'redirect' => $redirectUri ?: $this->getRedirectUri($provider)
        ];
    }
}