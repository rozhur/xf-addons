<?php 

namespace ZD\ESS\XF\Admin\Controller;

use XF\Mvc\ParameterBag;

class Template extends XFCP_Template
{
    public function actionEdit(ParameterBag $params)
    {
        if ($params->template_id)
        {
            return parent::actionEdit($params);
        }
        else
        {
            $title = $this->filter('title', 'str');
            if (!$title)
            {
                return $this->notFound();
            }

            /** @var \XF\Entity\Template $template */
            $template = $this->finder('XF:Template')
                ->where('title', $title)
                ->where('type', $this->filter('type', 'str', 'public'))
                ->fetchOne();
            if (!$template)
            {
                return $this->notFound();
            }

            $styleId = $this->filter('style_id', 'uint') ?: null;
            return $this->redirect($this->buildLink('templates/edit', $template, ['style_id' => $styleId]));
        }
    }
}
