<?php 

namespace ZD\ESS\XF\Service;

class ImageProxy extends XFCP_ImageProxy
{
    protected function finalizeFromFetchResults(\XF\Entity\ImageProxy $image, array $fetchResults)
    {
        $imageManager = $this->app->imageManager();
        $driver = $imageManager->imageFromFile($fetchResults['dataFile']);
        if ($driver && $driver->save($fetchResults['dataFile']) && ($type = $driver->getType()))
        {
            switch ($type)
            {
                case IMAGETYPE_JPEG: $extension = 'jpg'; break;
                case IMAGETYPE_PNG: $extension = 'png'; break;
                default: $extension = null;
            }

            if ($extension)
            {
                $pathInfo = pathinfo($fetchResults['fileName']);
                if (isset($pathInfo['filename']) && (!isset($pathInfo['extension']) || substr(strtolower($pathInfo['extension']), -strlen($extension)) !== $extension))
                {
                    $fetchResults['fileName'] = $pathInfo['filename'] . '.' . $extension;
                }
            }
        }

        parent::finalizeFromFetchResults($image, $fetchResults);
    }
}
