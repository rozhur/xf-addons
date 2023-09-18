<?php 

namespace ZD\ESS\XF\Criteria;

class User extends XFCP_User
{
    public function getExtraTemplateData()
    {
        $this->app->container()->set('zdessUserCriteriaSetup', true);
        $data = parent::getExtraTemplateData();
        $this->app->container()->set('zdessUserCriteriaSetup', false);
        return $data;
    }
}
