<?php

namespace ZD\IR;

use XF\AddOn\AbstractSetup;
use XF\AddOn\StepRunnerInstallTrait;
use XF\AddOn\StepRunnerUninstallTrait;
use XF\AddOn\StepRunnerUpgradeTrait;
use XF\Db\Schema\Alter;

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

    public function installStep2()
    {
        $this->applyGlobalPermission('general', 'editCustomLink');
        $this->applyGlobalPermission('forum', 'editThreadCustomLink');
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

    public function getColumnsOfTables()
    {
        $customLinkClosure = $this->createColumnClosure('varchar', 50, '');

        return [
            'xf_user' => ['zdir_custom_link' => $customLinkClosure],
            'xf_thread' => ['zdir_custom_link' => $customLinkClosure]
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
}