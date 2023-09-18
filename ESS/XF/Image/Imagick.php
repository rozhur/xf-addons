<?php 

namespace ZD\ESS\XF\Image;

class Imagick extends XFCP_Imagick
{
    public function save($file, $format = null, $quality = null)
    {
        if ($format === null && $format != IMAGETYPE_GIF && ($customFormat = \XF::options()['zdessConvertingUploadedImages']) != 'default')
        {
            $format = $customFormat;

            if ($format == IMAGETYPE_JPEG)
            {
                $image = $this->getImage();

                $image->borderImage('#ffffff', 1, 1);
                $image->trimImage(0);

                $this->type = $format;
            }
        }
        return parent::save($file, $format, $quality);
    }
}
