<?php

namespace ZD\ESS\Job;

use XF\Entity\ImageProxy;
use XF\Mvc\Entity\Entity;

class ImageProxyRebuild extends AbstractImageRebuild
{
    protected function getIdsToRebuild()
    {
        $db = $this->app->db();
        return $db->fetchAllColumn($db->limit(
            "
				SELECT image_id
				FROM xf_image_proxy
				WHERE image_id > ?
				ORDER BY image_id
			", $this->data['batch']
        ), $this->data['start']);
    }

    protected function getRecordToRebuild($id)
    {
        return $this->app->em()->find('XF:ImageProxy', $id);
    }

    protected function getFilePath(Entity $record)
    {
        /** @var ImageProxy $record */

        $id = $record->image_id;
        $group = floor($id / 1000);
        return \XF::getRootDirectory() . "/internal_data/image_cache/$group/$id-$record->url_hash.data";
    }

    protected function getStatusPhrase()
    {
        return \XF::phrase('images');
    }

    protected function postSave(Entity $record, $path, $type)
    {
        /** @var ImageProxy $record */

        $record->file_size = filesize($path);

        switch ($type)
        {
            case IMAGETYPE_JPEG: $extension = 'jpg'; break;
            case IMAGETYPE_PNG: $extension = 'png'; break;
            default: $extension = null;
        }

        if ($extension)
        {
            $pathInfo = pathinfo($record->file_name);
            if (substr(strtolower($pathInfo['extension']), -strlen($extension)) !== $extension)
            {
                $record->file_name = $pathInfo['filename'] . '.' . $extension;
            }
        }

        $record->save();
    }
}