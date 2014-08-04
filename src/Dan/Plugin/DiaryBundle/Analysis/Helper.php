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
        $properties = $report->getProperties();
        if (!isset($properties['date'])) {
            return null;
        }
        return substr($properties['date'],0,7);
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

    public function getHours($seconds)
    {
        $date = new \DateTime('today + '.$seconds.' seconds');

        return $date->format('H.i');
    }
}