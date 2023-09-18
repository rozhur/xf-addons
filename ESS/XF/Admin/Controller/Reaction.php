<?php

namespace ZD\ESS\XF\Admin\Controller;

use XF\Mvc\ParameterBag;

class Reaction extends XFCP_Reaction
{
    public function actionReset(ParameterBag $params)
    {
        $reaction = $this->assertReactionExists($params->reaction_id);
        if ($this->isPost())
        {
            $this->app->jobManager()->enqueue('ZD\ESS:ReactionReset', [
                'reactionId' => $reaction->reaction_id
            ], true);

            return $this->redirect($this->buildLink('reactions'));
        }
        else
        {
            $reactions = $this->finder('XF:ReactionContent')
                ->where('reaction_id', $reaction->reaction_id)
                ->fetch();

            return $this->view('XF:Reaction\Reset', 'zdess_reset_reaction_confirm', [
                'reaction' => $reaction,
                'reactionsToResetCount' => $reactions->count()
            ]);
        }
    }

    /** @param \ZD\ESS\XF\Entity\Reaction $reaction */
    public function reactionAddEdit(\XF\Entity\Reaction $reaction)
    {
        $view = parent::reactionAddEdit($reaction);

        $userCriteria = $this->app()->criteria('XF:User', $reaction->zdess_user_criteria);

        $view->setParam('userCriteria', $userCriteria);

        return $view;
    }

    /** @param \ZD\ESS\XF\Entity\Reaction $reaction */
    protected function reactionSaveProcess(\XF\Entity\Reaction $reaction)
    {
        $form = parent::reactionSaveProcess($reaction);

        $form->setup(function () use ($reaction)
        {
            $reaction->zdess_user_criteria = $this->filter('user_criteria', 'array');
        });

        return $form;
    }
}