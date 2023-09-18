<?php

namespace ZD\IC\Pub\Controller;

use XF\Entity\Widget;
use XF\Mvc\Controller;
use XF\Widget\WidgetRenderer;

class Ic extends Controller
{
    public function actionWidget()
    {
        $definitionId = $this->filter('definition_id', 'str');
        if (!$definitionId)
        {
            return $this->error(\XF::phrase('zdic_please_enter_widget_definition'));
        }

        /** @var Widget $widget */
        $widget = $this->finder('XF:Widget')->where('definition_id', $definitionId)->fetchOne();
        if (!$widget)
        {
            return $this->error(\XF::phrase('zdic_no_widget_defined_of_x', [
                'definition' => $definitionId
            ]));
        }

        /** @var WidgetRenderer $renderer */
        $renderer = $widget->getHandler()->render();


        file_put_contents('log.txt', "\n<|> " . \XF::visitor()->username . "\n" . $renderer->getTemplateName(), FILE_APPEND);
        $view = $this->view('ZD\IC:Ic\Widget', $renderer->getTemplateName(), $renderer->getViewParams());

        $view->setJsonParam('widget', true);

        return $view;
    }
}