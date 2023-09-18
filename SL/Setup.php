<?php

namespace ZD\SL;

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

    public function getColumnsOfTables()
    {
        return [
            'xf_user' => [
                'zdsl_seller' => $this->createColumnClosure('bool', null, 0)
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

    public function uninstallStep1() {
        foreach ($this->getColumnsOfTables() as $tableName => $columns)
        {
            $this->schemaManager()->alterTable($tableName, function (Alter $alter) use ($columns)
            {
                $alter->dropColumns(array_keys($columns));
            });
        }
    }
}