<?php

namespace ZD\ESS\Job;

use XF\Job\AbstractJob;
use XF\Mvc\Entity\Entity;

abstract class AbstractImageRebuild extends AbstractJob
{
    protected $defaultData = [
        'start' => 0,
        'batch' => 50
    ];

    abstract protected function getIdsToRebuild();
    abstract protected function getRecordToRebuild($id);
    abstract protected function getFilePath(Entity $record);
    protected function postSave(Entity $record, $path, $type) {}
    abstract protected function getStatusPhrase();

    public function run($maxRunTime)
    {
        $startTime = microtime(true);

        $imageManager = $this->app->imageManager();

        $ids = $this->getIdsToRebuild();
        if (!$ids)
        {
            return $this->complete();
        }

        $done = 0;

        foreach ($ids AS $id)
        {
            $this->data['start'] = $id;

            $record = $this->getRecordToRebuild($id);
            $path = $this->getFilePath($record);

            $image = $imageManager->imageFromFile($path);
            if ($image && $image->save($path) && ($type = $image->getType()))
            {
                $this->postSave($record, $path, $type);
                $done++;
            }
        }

        $this->data['batch'] = $this->calculateOptimalBatch($this->data['batch'], $done, $startTime, $maxRunTime, 1000);

        return $this->resume();
    }

    public function getStatusMessage()
    {
        $actionPhrase = \XF::phrase('rebuilding');
        $typePhrase = $this->getStatusPhrase();
        return sprintf('%s... %s (%s)', $actionPhrase, $typePhrase, $this->data['start']);
    }

    public function canCancel()
    {
        return true;
    }

    public function canTriggerByChoice()
    {
        return true;
    }
}