<?php
namespace Dan\Plugin\DiaryBundle\Analysis;

use Symfony\Component\Yaml\Yaml;
use Dan\Plugin\DiaryBundle\Entity\Report;

class Helper
{

    public function getProject(Report $report)
    {
        $properties = $report->getProperties();
        if (!isset($properties['project'])) {
            return null;
        }
        return $properties['project'];
    }

    public function getMonth(Report $report)
    {
        $date = $this->getDate($report);
        if (!$date) {
            return null;
        }
        return $date->format('Y-m');
    }

    public function getDate(Report $report)
    {
        $properties = $report->getProperties();
        if (!isset($properties['date'])) {
            return null;
        }
        return new \DateTime($properties['date']);
    }

    public function getTotalTime(Report $report)
    {
        $today = new \DateTime('today');

        $properties = $report->getProperties();
        $seconds = 0;
        if (isset($properties['times'])) {
            $times = $properties['times'];
            foreach($times as $time) {
                $time = explode(' - ', $time);

                $from = new \DateTime($today->format('Y-m-d').' '.$time[0]);
                $to = new \DateTime($today->format('Y-m-d').' '.$time[1]);

                $diff = $from->diff($to);
                if ($diff->format('%r')=='-') {
                    $to->modify('+1 day');
                    $diff = $from->diff($to);
                }

                $seconds += $diff->format('%s');
                $seconds += $diff->format('%i') * 60;
                $seconds += $diff->format('%h')* 60 * 60;

            }
        }

        return $seconds;
    }

    public function getAsHours($seconds)
    {
        $hours = floor($seconds / (60*60));
        $minutes = str_pad(($seconds % (60*60))*60,2,'0',STR_PAD_LEFT);

        return $hours.'.'.$minutes;
    }
}