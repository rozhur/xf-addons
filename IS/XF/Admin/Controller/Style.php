<?php 

namespace ZD\IS\XF\Admin\Controller;

use XF\Mvc\ParameterBag;

class Style extends XFCP_Style
{
    public function actionTemplates(ParameterBag $params)
    {
        $type = $this->filter('type', 'str');
        if (!$type)
        {
            $type = 'public';
        }

        $currentAddOn = null;
        $addOnId = $this->filter('addon_id', 'str');
        if ($addOnId)
        {
            $currentAddOn = $this->em()->find('XF:AddOn', $addOnId);
        }

        $style = $this->assertStyleExists($params->style_id);
        if (!$style->canEdit())
        {
            return $this->error(\XF::phrase('templates_in_this_style_can_not_be_modified'));
        }

        $this->app->response()->setCookie('edit_style_id', $style->style_id);

        $page = $this->filterPage();
        $perPage = 300;

        $templateRepo = $this->getTemplateRepo();
        $types = $templateRepo->getTemplateTypes($style);
        if (!isset($types[$type]))
        {
            return $this->error(\XF::phrase('templates_in_this_style_can_not_be_modified'));
        }

        $templateFinder = $templateRepo->findEffectiveTemplatesInStyle($style, $type);
        $templateFinder->limitByPage($page, $perPage);

        if ($currentAddOn)
        {
            $templateFinder->where('Template.addon_id', $currentAddOn->addon_id);
        }
        $templateFinder->with('Template.AddOn');

        $filter = $this->filter('_xfFilter', [
            'text' => 'str',
            'prefix' => 'bool'
        ]);
        if (strlen($filter['text']))
        {
            $templateFinder->Template->searchTitle($filter['text'], $filter['prefix']);
        }

        $templates = $templateFinder->fetch();
        $total = $templateFinder->total();

        $linkParams = [
            'type' => $type,
            'addon_id' => $currentAddOn ? $currentAddOn->addon_id : null
        ];

        $viewParams = [
            'style' => $style,
            'types' => $types,
            'type' => $type,
            'templates' => $templates,
            'styleTree' => $this->getStyleRepo()->getStyleTree(),

            'currentAddOn' => $currentAddOn,
            'addOns' => $this->getAddOnRepo()->findAddOnsForList()->fetch(),

            'linkParams' => $linkParams,
            'filter' => $filter['text'],

            'page' => $page,
            'perPage' => $perPage,
            'total' => $total
        ];
        return $this->view('XF:Template\Listing', 'template_list', $viewParams);
    }
}
