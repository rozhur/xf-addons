<?php 

namespace ZD\ESS\XF\Service\Attachment;

class Preparer extends XFCP_Preparer
{
    public function insertDataFromFile(\XF\FileWrapper $file, $userId, array $extra = [])
    {
        if (\XF::options()['zdessConvertingUploadedImages'] != 'default')
        {
            $sourceFile = $file->getFilePath();

            $imageManager = $this->app->imageManager();
            $image = $imageManager->imageFromFile($sourceFile);

            if ($image && $image->save($sourceFile))
            {
                $filename = pathinfo($file->getFileName(), PATHINFO_FILENAME);
                $type = $image->getType();
                if ($type)
                {
                    switch ($type)
                    {
                        case IMAGETYPE_JPEG:
                            $filename .= '.jpg';
                            break;
                        case IMAGETYPE_PNG:
                            $filename .= '.png';
                            break;
                        default:
                            return parent::insertDataFromFile($file, $userId, $extra);
                    }
                    $file = new \XF\FileWrapper($sourceFile, $filename);
                }
            }
        }
        return parent::insertDataFromFile($file, $userId, $extra);
    }
}
