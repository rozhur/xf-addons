<?php

namespace ZD\ESS\XF;

class Language extends XFCP_Language
{
    public function getDateFormatWithoutYear()
    {
        $format = $this->options['date_format'];

        return trim(preg_replace('#[^Mj ]#', '', $format), ' ');
    }

    public function getDateTimeOutput($date, $time)
    {
        return $date;
    }

    public function getDateTimeParts($timestamp)
    {
        $parts = parent::getDateTimeParts($timestamp);

        $timestampYear = date('Y', $timestamp);
        $currentYear = date('Y', \XF::$time);
        if ($timestampYear != $currentYear)
        {
            return $parts;
        }

        $dateObj = new \DateTime('@' . $timestamp);
        $dateObj->setTimezone($this->getTimeZone());

        $parts[0] = trim(str_replace($currentYear, '', $parts[0]));
        $parts[0] = strtr($this->getPhraseCacheRaw('date_x_at_time_y'), [
            '{date}' => $parts[0],
            '{time}' => $parts[1]
        ]);
        return $parts;
    }
}