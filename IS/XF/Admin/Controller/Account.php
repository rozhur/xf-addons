<?php 

namespace ZD\IS\XF\Admin\Controller;

class Account extends XFCP_Account
{
    public function actionStyle()
    {
        $visitor = \XF::visitor();
        if (!$visitor->canChangeStyle($error))
        {
            return $this->noPermission($error);
        }

        $redirect = $this->getDynamicRedirect(null, true);

        $csrfValid = true;
        if ($visitor->user_id)
        {
            $csrfValid = $this->validateCsrfToken($this->filter('t', 'str'));
        }

        if ($this->request->exists('style_id') && $csrfValid)
        {
            $styleId = $this->filter('style_id', 'uint');
            $style = $this->app->style($styleId);

            if ($style['user_selectable'] || $visitor->hasAdminPermission('style'))
            {
                if ($visitor->user_id)
                {
                    /** @var \ZD\IS\XF\Entity\Admin $admin */
                    $admin = $visitor->Admin;
                    $admin->zdis_admin_style_id = $styleId;
                    $admin->save();

                    $this->app->response()->setCookie('style_id', false);
                }
                else
                {
                    $this->app->response()->setCookie('style_id', $style->getId());
                }
            }

            return $this->redirect($redirect);
        }
        else
        {
            $styles = $this->repository('XF:Style')->getUserSelectableStyles();

            $styleId = $this->filter('style_id', 'uint');
            if ($styleId && !empty($styles[$styleId]['user_selectable']))
            {
                $style = $styles[$styleId];
            }
            else
            {
                $style = false;
            }

            $viewParams = [
                'redirect' => $redirect,
                'style' => $style,
                'styles' => $styles
            ];
            return $this->view('XF:Account\Style', 'zdis_style_chooser', $viewParams);
        }
    }
}
