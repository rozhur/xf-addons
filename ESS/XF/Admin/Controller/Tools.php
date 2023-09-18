<?php

namespace ZD\ESS\XF\Admin\Controller;

class Tools extends XFCP_Tools
{
    public function actionPhpinfo()
    {
        $this->assertAdminPermission('zdessDevelopment');

        parent::actionPhpinfo();
    }
}