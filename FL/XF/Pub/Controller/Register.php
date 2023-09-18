<?php

namespace ZD\FL\XF\Pub\Controller;

use XF\ConnectedAccount\ProviderData\AbstractProviderData;
use XF\Entity\ConnectedAccountProvider;
use ZD\FL\ConnectedAccount\ProviderData\Vkontakte;

class Register extends XFCP_Register
{
    protected function getConnectedRegisterResponse(array $viewParams)
    {
        /** @var ConnectedAccountProvider $provider */
        $provider = $viewParams['provider'];
        $response = parent::getConnectedRegisterResponse($viewParams);
        if ($provider->provider_id == 'zdfl_vk')
        {
            $response->setTemplateName("zdfl_vk_register_connected_account");
        }

        return $response;
    }

    protected function setupConnectedRegistration(array $input, AbstractProviderData $providerData)
    {
        if ($providerData instanceof Vkontakte)
        {
            $input['email'] = '';
            $registration = parent::setupConnectedRegistration($input, $providerData);
            $registration->skipEmailConfirmation();
            $user = $registration->getUser();
            $user->setOption('admin_edit', true);
            $user->email = '';
        }
        else
        {
            $registration = parent::setupConnectedRegistration($input, $providerData);
        }
        return $registration;
    }
}