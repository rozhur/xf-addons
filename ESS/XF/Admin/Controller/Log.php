<?php

namespace ZD\ESS\XF\Admin\Controller;

use XF\Mvc\ParameterBag;

class Log extends XFCP_Log
{
    public function actionServerError(ParameterBag $params)
    {
        $this->assertAdminPermission('zdessViewErrorLogs');

        return parent::actionServerError($params);
    }

    public function actionServerErrorClear()
    {
        $this->assertAdminPermission('zdessViewErrorLogs');

        return parent::actionServerErrorClear();
    }

    public function actionServerErrorDelete(ParameterBag $params)
    {
        $this->assertAdminPermission('zdessViewErrorLogs');

        return parent::actionServerErrorDelete($params);
    }

    public function actionEmailBounces()
    {
        $this->assertAdminPermission('zdessViewErrorLogs');

        return parent::actionEmailBounces();
    }
}