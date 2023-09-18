<?php 

namespace ZD\ESS\XF\Service\User;

class Warn extends XFCP_Warn
{
    protected $alert = false;

    public function withAlert($alert = true)
    {
        $this->alert = $alert;
    }

    protected function _save()
    {
        $warning = parent::_save();
        if ($this->alert)
        {
            $alertRepo = $this->repository('XF:UserAlert');
            $alertRepo->alert(
                $warning->User, 0, '',
                'warning', $warning->warning_id,
                'insert',
                [
                    'title' => $warning->title,
                    'warningLink' => $this->app->router()->buildLink('warnings', $warning)
                ]
            );
        }

        return $warning;
    }
}
