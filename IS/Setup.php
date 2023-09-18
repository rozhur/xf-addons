<?php

namespace ZD\IS;

use XF\AddOn\AbstractSetup;
use XF\AddOn\StepRunnerInstallTrait;
use XF\AddOn\StepRunnerUninstallTrait;
use XF\AddOn\StepRunnerUpgradeTrait;
use XF\Db\Schema\Alter;
use XF\Db\Schema\Create;

class Setup extends AbstractSetup
{
	use StepRunnerInstallTrait;
	use StepRunnerUpgradeTrait;
	use StepRunnerUninstallTrait;

    public function installStep1()
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

    public function postInstall(array &$stateChanges)
    {
        $this->rebuildAll();
    }

    public function postRebuild()
    {
        $this->rebuildAll();
    }

    public function uninstallStep1() {
        foreach ($this->getColumnsOfTables() as $tableName => $columns)
        {
            $this->schemaManager()->alterTable($tableName, function (Alter $alter) use ($columns)
            {
                $alter->dropColumns(array_keys($columns));
            });
        }
    }

    public function uninstallStep2()
    {
        $this->rebuildAll();
    }

    public function getColumnsOfTables()
    {
        return [
            'xf_user_group' => [
                'zdis_username_css_class' => $this->createColumnClosure('varchar', 75, 0)
            ],
            'xf_admin' => [
                'zdis_admin_style_id' => $this->createColumnClosure('int', 10, 0)
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
        /** @var \ZD\IS\XF\Repository\UserGroup $userGroupRepo */
        $userGroupRepo = $this->app()->repository('XF:UserGroup');

        \XF::runOnce('userGroupRebuild', function() use ($userGroupRepo)
        {
            $userGroupRepo->rebuildDisplayStyleCache();
            $userGroupRepo->rebuildUserBannerCache();
        });

        $this->app()->container()['zdisIgnoreAdminStyle'] = true;

        $this->app()->jobManager()->enqueueUnique('styleRebuild', 'XF:Atomic', [
            'execute' => ['XF:TemplateRebuild', 'XF:StyleAssetRebuild', 'XF:StylePropertyRebuild']
        ]);
    }
}