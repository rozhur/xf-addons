<?php

namespace ZD\ESS\XF\Admin\Controller;

use XF\Mvc\ParameterBag;
use XF\Mvc\Reply\Redirect;

class TemplateModification extends XFCP_TemplateModification
{
    public function actionSave(ParameterBag $params)
    {
        $result = parent::actionSave($params);

        if ($result instanceof Redirect && !$params['modification_id'] && !$this->request->exists('exit'))
        {
            $modificationKey = $this->filter('modification_key', 'str');

            /** @var \XF\Entity\TemplateModification $modification */
            $modification = $this->finder('XF:TemplateModification')->where('modification_key', $modificationKey)->fetchOne();
            if ($modification)
            {
                return $this->redirect($this->buildLink('template-modifications/edit', $modification));
            }
        }

        return $result;
    }
}