<?php 

namespace ZD\ESS\XF\Validator;

class Username extends XFCP_Username
{
    public function setupOptionDefaults()
    {
        parent::setupOptionDefaults();

        $visitor = \XF::visitor();
        if ($visitor->hasPermission('general', 'bypassUsernameLength'))
        {
            $this->setOption('length_min', 0);
            $this->setOption('length_max', 50);
        }

        if ($visitor->hasPermission('general', 'bypassUsernameRegex'))
        {
            $this->setOption('regex_match', null);
        }

        if ($visitor->is_moderator || $visitor->is_admin)
        {
            $this->setOption('disallowed_contain', []);
            $this->setOption('allow_censored', true);
        }
    }
}
