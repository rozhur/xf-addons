<?php

namespace ZD\ESS;

use XF\AddOn\AbstractSetup;
use XF\AddOn\StepRunnerInstallTrait;
use XF\AddOn\StepRunnerUninstallTrait;
use XF\AddOn\StepRunnerUpgradeTrait;
use XF\Db\Schema\Alter;
use XF\Db\Schema\Create;
use XF\Entity\Option;
use ZD\ESS\XF\Entity\AdminNavigation;

class Setup extends AbstractSetup
{
	use StepRunnerInstallTrait;
	use StepRunnerUpgradeTrait;
	use StepRunnerUninstallTrait;

    public function installStep1()
    {
        foreach ($this->getAddonTables() as $tableName => $closure)
        {
            $this->schemaManager()->createTable($tableName, $closure);
        }
    }

    public function installStep2()
    {
        foreach ($this->getColumnsOfTables() as $tableName => $columns)
        {
            $this->schemaManager()->alterTable($tableName, function (Alter $alter) use ($columns)
            {
                foreach ($columns as $column => $closure)
                {
                    $closure($alter, $column);
                }
            });
        }
    }

    public function installStep3()
    {
        $this->applyGlobalPermission('profilePost', 'stickProfilePost');
        $this->applyGlobalPermission('general', 'deleteUsernameHistory');
    }

    public function installStep4()
    {
        $this->createWidget('zdess_member_view_sidebar_followers', 'zdess_member_followers', [
            'positions' => ['member_view_sidebar' => 25]
        ]);
        $this->createWidget('zdess_member_view_sidebar_following', 'zdess_member_following', [
            'positions' => ['member_view_sidebar' => 30]
        ]);
        $this->createWidget('zdess_member_view_sidebar_reactions', 'zdess_member_reactions', [
            'positions' => ['member_view_sidebar' => 100]
        ]);
    }

    public function postInstall(array &$stateChanges)
    {
        $option = $this->getRegistrationDefaultsOption();
        if ($option)
        {
            $value = $option->default_value;
            $value['zdess_show_reg_date'] = '1';
            $option->default_value = json_encode($value);
            $option->save();
        }

        $adminNavigations = $this->getAdminNavigations(['admins', 'adminLog']);
        foreach ($adminNavigations as $adminNavigation)
        {
            $adminNavigation->zdess_super_admins_only = true;
            $adminNavigation->save();
        }

        $adminNavigations = $this->getAdminNavigations(['serverErrorLog', 'emailBounceLog']);
        foreach ($adminNavigations as $adminNavigation)
        {
            $adminNavigation->admin_permission_id = 'zdessViewErrorLogs';
            $adminNavigation->save();
        }

        $this->rebuildAll();
        $this->app()->jobManager()->enqueueUnique('zdessReactionCountRebuild', 'ZD\ESS:ReactionRebuild');
    }

    public function postRebuild()
    {
        $this->rebuildAll();
    }

    public function uninstallStep1() {
        foreach (array_keys($this->getAddonTables()) as $tableName)
        {
            $this->schemaManager()->dropTable($tableName);
        }
    }

    public function uninstallStep2() {
        foreach ($this->getColumnsOfTables() as $tableName => $columns)
        {
            $this->schemaManager()->alterTable($tableName, function (Alter $alter) use ($columns)
            {
                $alter->dropColumns(array_keys($columns));
            });
        }
    }

    public function uninstallStep3()
    {
        $option = $this->getRegistrationDefaultsOption();

        if ($option)
        {
            $value = $option->default_value;
            unset($value['zdess_show_reg_date']);
            $option->default_value = json_encode($value);
            $option->save();
        }

        $adminNavigation = $this->getAdminNavigations('serverErrorLog');
        $adminNavigation->admin_permission_id = 'viewLogs';
        $adminNavigation->save();

        $this->rebuildAll();
    }

    public function getAddonTables()
    {
        return [
            'zd_ess_reaction_count' => function (Create $table) {
                $table->addColumn('reaction_count_id', 'int', 10)->autoIncrement();
                $table->addColumn('reaction_id', 'int', 10);
                $table->addColumn('user_id', 'int', 10);
                $table->addColumn('received', 'int', 10)->setDefault(0);
                $table->addColumn('given', 'int', 10)->setDefault(0);
                $table->addUniqueKey(['reaction_id', 'user_id']);
            }
        ];
    }

    public function getColumnsOfTables()
    {
        return [
            'xf_user_group' => [
                'zdess_disable_grouping' => $this->createColumnClosure('tinyint', null, 1),
                'zdess_super_user_group' => $this->createColumnClosure('tinyint', null, 0)
            ],
            'xf_admin_navigation' => [
                'zdess_super_admins_only' => $this->createColumnClosure('tinyint', null, 0)
            ],
            'xf_user_option' => [
                'zdess_show_reg_date' => $this->createColumnClosure('tinyint', null, 1)
            ],
            'xf_profile_post' => [
                'zdess_sticky' => $this->createColumnClosure('tinyint', null, 0),
                'zdess_real_user_id' => $this->createColumnClosure('int', 10, 0)
            ],
            'xf_profile_post_comment' => [
                'zdess_real_user_id' => $this->createColumnClosure('int', 10, 0)
            ],
            'xf_post' => [
                'zdess_real_user_id' => $this->createColumnClosure('int', 10, 0)
            ],
            'xf_conversation_message' => [
                'zdess_real_user_id' => $this->createColumnClosure('int', 10, 0)
            ],
            'xf_thread' => [
                'zdess_disallow_open_discussion' => $this->createColumnClosure('tinyint', 1, 0)
            ],
            'xf_forum' => [
                'zdess_forum_open' => $this->createColumnClosure('tinyint', null, 1)
            ],
            'xf_reaction' => [
                'zdess_user_criteria' => $this->createColumnClosure('mediumblob')
            ],
            'xf_user' => [
                'zdess_from_mobile' => $this->createColumnClosure('tinyint', 1, 0),
                'zdess_behalf_criteria' => $this->createColumnClosure('mediumblob'),
                'zdess_reaction_score_positive' => $this->createColumnClosure('int', 10, 0),
                'zdess_reaction_score_negative' => $this->createColumnClosure('int', 10, 0),
            ],
            'xf_session_activity' => [
                'zdess_from_mobile' => $this->createColumnClosure('tinyint', null, 0)
            ],
            'xf_admin' => [
                'zdess_admin_style_id' => $this->createColumnClosure('int', 10, 0),
                'zdess_available_moderator_permissions' => $this->createColumnClosure('mediumblob')
            ]
        ];
    }

    protected function createColumnClosure($type, $length = null, $default = null)
    {
        return function (Alter $alter, $columnName) use ($type, $length, $default)
        {
            $column = $alter->addColumn($columnName, $type, $length);

            if ($default !== null)
            {
                $column->setDefault($default);
            }
        };
    }

    protected function rebuildAll()
    {
        /** @var \ZD\ESS\XF\Repository\UserGroup $userGroupRepo */
        $userGroupRepo = $this->app()->repository('XF:UserGroup');
        \XF::runOnce('displayStyleRebuild', function() use ($userGroupRepo)
        {
            $userGroupRepo->rebuildDisplayStyleCache();
            $userGroupRepo->rebuildUserBannerCache();
        });

        \XF::runOnce('userBannerRebuild', function() use ($userGroupRepo)
        {
            $userGroupRepo->rebuildUserBannerCache();
        });

        /** @var \XF\Repository\UserTitleLadder $userTitleLadderRepo */
        $userTitleLadderRepo = $this->app()->repository('XF:UserTitleLadder');
        \XF::runOnce('userTitleLadderRebuild', function() use ($userTitleLadderRepo)
        {
            $userTitleLadderRepo->rebuildLadderCache();
        });

        /** @var \XF\Repository\AdminNavigation $navigationRepo */
        $navigationRepo = $this->app()->repository('XF:AdminNavigation');
        \XF::runOnce('adminNavigationRebuild', function() use ($navigationRepo)
        {
            $navigationRepo->rebuildNavigationCache();
        });

        foreach ($userGroupRepo->findUserGroupsForList()->fetch() as $userGroup)
        {
            $userGroup->saveMasterPhrases();
        }

        foreach ($userTitleLadderRepo->findLadder()->fetch() as $ladder)
        {
            $ladder->getMasterPhrase(true)->save();
        }
    }

    /** @return Option */
    protected function getRegistrationDefaultsOption()
    {
        return $this->app()->em()->find('XF:Option', 'registrationDefaults');
    }

    /** @return AdminNavigation|AdminNavigation[] */
    protected function getAdminNavigations($ids)
    {
        $finder = $this->app()->finder('XF:AdminNavigation');

        if (is_array($ids))
        {
            return $finder->whereIds($ids)->fetch();
        }
        else
        {
            return $finder->whereId($ids)->fetchOne();
        }
    }
}