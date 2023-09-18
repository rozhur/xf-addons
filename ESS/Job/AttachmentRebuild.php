<?php

namespace ZD\ESS\Job;

use XF\Entity\AttachmentData;
use XF\Mvc\Entity\Entity;

class AttachmentRebuild extends AbstractImageRebuild
{
    protected function getIdsToRebuild()
    {
        $db = $this->app->db();
        return $db->fetchAllColumn($db->limit(
            "
				SELECT data_id
				FROM xf_attachment_data
				WHERE data_id > ?
				ORDER BY data_id
			", $this->data['batch']
        ), $this->data['start']);
    }

    protected function getRecordToRebuild($id)
    {
        return $this->app->em()->find('XF:AttachmentData', $id);
    }

    protected function getFilePath(Entity $record)
    {
        /** @var AttachmentData $record */

        $id = $record->data_id;
        $group = floor($id / 1000);
        return \XF::getRootDirectory() . "/internal_data/attachments/$group/$id-$record->file_hash.data";
    }

    protected function getStatusPhrase()
    {
        return \XF::phrase('attachments');
    }

    protected function postSave(Entity $record, $path, $type)
    {
        /** @var AttachmentData $record */

        $record->file_size = filesize($path);
        $record->save();

        $id = $record->data_id;
        $group = floor($id / 1000);
        $thumbnailPath = \XF::getRootDirectory() . "/data/attachments/$group/$id-$record->file_hash.jpg";

        $imageManager = $this->app->imageManager();
        $image = $imageManager->imageFromFile($thumbnailPath);
        if ($image)
        {
            $image->save($thumbnailPath);
        }
    }
}