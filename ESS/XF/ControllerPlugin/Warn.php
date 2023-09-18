<?php 

namespace ZD\ESS\XF\ControllerPlugin;

use XF\Mvc\Entity\Entity;

class Warn extends XFCP_Warn
{
    protected function setupWarnService(\XF\Warning\AbstractHandler $warningHandler, \XF\Entity\User $user, $contentType, Entity $content, array $input)
    {
        /** @var \ZD\ESS\XF\Service\User\Warn $service */
        $service = parent::setupWarnService($warningHandler, $user, $contentType, $content, $input);

        if ($input['zdess_alert'])
        {
            $service->withAlert();
        }

        return $service;
    }

    protected function getWarnSubmitInput()
    {
        $input = $this->filter([
            'zdess_alert' => 'bool'
        ]);
        return parent::getWarnSubmitInput() + $input;
    }
}
