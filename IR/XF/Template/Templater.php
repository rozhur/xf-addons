<?php

namespace ZD\IR\XF\Template;

class Templater extends XFCP_Templater
{
    public function fnCopyright($templater, &$escape)
    {
        $copyright = parent::fnCopyright($templater, $escape);

        $app = $this->app;
        if (!$app->container('zdDisableCopyright'))
        {
            if ($copyright)
            {
                $copyright .= " | ";
            }
            $addonsPhrase = \XF::phrase('admin_navigation.addOns')->render();
            $byPhrase = \XF::phrase('by')->render();
            $byPhrase = mb_strtolower($byPhrase);

            $copyright .= "$addonsPhrase $byPhrase <a href=\"https://xf.zhdev.org\" target=\"_blank\">xf.zhdev.org</a>";
            $app->container()['zdDisableCopyright'] = true;
        }
        return $copyright;
    }
}
