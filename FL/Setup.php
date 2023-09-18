<?php

namespace ZD\FL;

use XF\AddOn\AbstractSetup;
use XF\AddOn\StepRunnerInstallTrait;
use XF\AddOn\StepRunnerUninstallTrait;
use XF\AddOn\StepRunnerUpgradeTrait;

class Setup extends AbstractSetup
{
    use StepRunnerInstallTrait;
    use StepRunnerUpgradeTrait;
    use StepRunnerUninstallTrait;

    public function installStep1()
    {
        $this->db()->insertBulk('xf_connected_account_provider', [
            [
                'provider_id' => 'zdfl_vk',
                'provider_class' => 'ZD\\FL:Provider\\Vkontakte',
                'display_order' => 5,
                'options' => ''
            ]
        ], 'provider_id');
    }

    public function uninstallStep1()
    {
        $this->db()->delete('xf_connected_account_provider', 'provider_class LIKE ?', 'ZD\\\\FL:Provider\\\\%');
    }

    public function uninstallStep2()
    {
        $this->db()->delete('xf_user_connected_account', 'provider LIKE ?', 'zdfl\_%');
    }
}