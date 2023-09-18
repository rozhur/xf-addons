<?php 

namespace ZD\ESS\XF\Image;

class Gd extends XFCP_Gd
{
    public function save($file, $format = null, $quality = null)
    {
        if ($format === null && $format != IMAGETYPE_GIF && ($customFormat = \XF::options()['zdessConvertingUploadedImages']) != 'default')
        {
            $format = $customFormat;

            if ($format == IMAGETYPE_JPEG)
            {
                $oldImage = $this->getImage();
                $width = $this->getWidth();
                $height = $this->getHeight();

                $this->createImage($this->getWidth(), $this->getHeight());
                $newImage = $this->getImage();

                $color = imagecolorallocate($newImage, 255, 255, 255);
                imagefilledrectangle($newImage, 0, 0, $width, $height, $color);
                imagecopy($newImage, $oldImage, 0, 0, 0, 0, $width, $height);
            }

            $this->type = $format;
        }
        return parent::save($file, $format, $quality);
    }
}
