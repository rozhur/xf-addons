<?php

namespace ZD\ESS\XF\Pub\Controller;

use ZD\ESS\XF\Entity\UserOption;

class Account extends XFCP_Account
{
    public function actionWarnings()
    {
        $visitor = \XF::visitor();

        $page = $this->filterPage();
        $perPage = $this->options()->zdessWarningsPerPage;

        /** @var \XF\Repository\Warning $warningRepo */
        $warningRepo = $this->repository('XF:Warning');
        $warningsFinder = $warningRepo->findUserWarningsForList($visitor->user_id)
            ->where('is_expired', false);

        $totalWarnings = $warningsFinder->total();
        if (!$totalWarnings)
        {
            return $this->redirect($this->buildLink('account'));
        }

        $warnings = $warningsFinder
            ->limitByPage($page, $perPage)
            ->fetch();

        $view = $this->view('XF:Account\AccountWarnings', 'zdess_account_warnings', [
            'warnings' => $warnings,
            'page' => $page,
            'perPage' => $perPage,
            'totalWarnings' => $totalWarnings
        ]);
        return $this->addAccountWrapperParams($view, 'zdess_account_warnings');
    }

    protected function savePrivacyProcess(\XF\Entity\User $visitor)
    {
        $form = parent::savePrivacyProcess($visitor);

        $form->setup(function () use ($visitor)
        {
            $input = $this->filter(['option' => ['zdess_show_reg_date' => 'bool']]);

            /** @var UserOption $userOptions */
            $userOptions = $visitor->getRelationOrDefault('Option');
            $userOptions->zdess_show_reg_date = $input['option']['zdess_show_reg_date'];
        });

        return $form;
    }
}