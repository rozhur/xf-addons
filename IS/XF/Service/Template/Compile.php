<?php 

namespace ZD\IS\XF\Service\Template;

use XF\Entity\Template;

class Compile extends XFCP_Compile
{
    protected function getApplicableStyleIds(Template $template)
    {
        return $this->db()->fetchAllColumn("
			SELECT style_id
			FROM xf_template_map
			WHERE template_id = ?
		", $template->template_id);
    }
}
