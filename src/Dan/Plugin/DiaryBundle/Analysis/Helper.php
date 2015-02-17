<?php
namespace Dan\Plugin\DiaryBundle\Analysis;

use Symfony\Component\Yaml\Yaml;
use Dan\Plugin\DiaryBundle\Model\ReportChild as Report;

use Dan\MainBundle\Model\ArrayHelper;

class Helper
{
    
    public function __construct()
    {
        $this->arrayHelper = new ArrayHelper();
    }
    
    private function getPropertyValue($report, $path)
    {
        $properties = $report->getProperties();
        return $this->arrayHelper->getPath($properties, $path);
    }
    
    private function getPropertyValues($report, $path)
    {
        $properties = $report->getProperties();
        $pathes = $this->arrayHelper->explodePath($properties, $path);
        $values = array();
        foreach($pathes as $path) {
            $values[] = $this->arrayHelper->getPath($properties, $path);
        }

        return $values;
    }
    
    
    public function getProject(Report $report)
    {
        return $this->getPropertyValue($report, 'project');
    }
    
    public function getDate(Report $report)
    {
        $date = $this->getPropertyValue($report, 'date');
        if (!$date) {
            return null;
        }
        return new \DateTime($date);
    }

    public function getMonth(Report $report)
    {
        $date = $this->getDate($report);
        if (!$date) {
            return null;
        }
        return $date->format('Y-m');
    }

   
    

//    public function getProjects(Report $report)
//    {
//        $projects = $this->getPropertyValues($report, 'dates.*.projects.*.project');
//        return array_unique($projects);
//    }
//
//
//    public function getDates(Report $report)
//    {
//        $dates = $this->getPropertyValues($report, 'dates.*.date');
//        $dates = array_unique($dates);
//        foreach($dates as $i => $date) {
//            $dates[$i] = new \DateTime($date);
//        }
//        return $dates;
//    }
//
//    public function getMonths(Report $report)
//    {
//        $dates = $this->getDates($report);
//        foreach($dates as $i => $date) {
//            $dates[$i] = $date->format('Y-m');
//        }
//        $dates = array_unique($dates);
//        return $dates;
//    }

    public function getTasks(Report $report)
    {
        $properties = $report->getProperties();
        if (!isset($properties['tasks'])) {
            return array();
        }
        return $properties['tasks'];
    }

    public function getTotalTime(Report $report)
    {
        $today = new \DateTime('today');

        $properties = $report->getProperties();
        $seconds = 0;
        if (isset($properties['time_ranges'])) {
            $times = $properties['time_ranges'];
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
        if (isset($properties['time_mods'])) {
            $timesMods = $properties['time_mods'];
            foreach($timesMods as $mod) {
                if (preg_match('/^(?P<sign>[\+\-])(?P<num>\d{1,2})(?P<unit>[m])$/', $mod, $matches)) {
                    $seconds += ($matches['sign'].$matches['num']) * 60;
                    continue;
                }
                if (preg_match('/^(?P<sign>[\+\-])(?P<num>\d{1,2})(?P<unit>[h])$/', $mod, $matches)) {
                    $seconds += ($matches['sign'].$matches['num']) * 60 * 60;
                    continue;
                }
                if (preg_match('/^(?P<sign>[\+\-])(?P<hours>\d{1,2})[\.](?P<minutes>\d{2})/', $mod, $matches)) {
                    $seconds += ($matches['sign'].'1')*(($matches['hours'] * 60 * 60) + ($matches['minutes'] * 60));
                    continue;
                }
            }
        }

        return $seconds;
    }

    public function getAsHours($seconds)
    {
        $sign = '';
        if ($seconds<0) {
            $sign = '-';
            $seconds = abs($seconds);
        }
        $hours = floor($seconds / (60*60));
        $minutes = str_pad(($seconds % (60*60))/60,2,'0',STR_PAD_LEFT);

        return $sign.$hours.'.'.$minutes;
    }

    public function getAsHtml($text)
    {
        if (is_array($text)) {
            foreach($text as $key => $value) {
                $text[$key] = $this->getAsHtml($value);
            }
            return $text;
        }

        $placeholders = array();
        $pattern = '/(?P<ref>http[s]?:\/\/[^\s]+)/';
        while(preg_match($pattern, $text, $matches)) {

            $placeholder = $matches['ref'];
            $placeholders[] = $placeholder; 

            $value = strtr(preg_quote($placeholder), array('/' => '\\/'));
            $text = preg_replace('/'.$value.'/', '{{'.(count($placeholders)-1).'}}', $text, 1);
        }

        $html = $text;
        $pattern = '/{{(?P<i>\d+)}}/';

        while (preg_match($pattern, $html, $matches)) {
            if (!isset($placeholders[(int)$matches['i']])) {
                break;
            }
            $placeholder = $placeholders[(int)$matches['i']];
            $replacement = '<a href="'.$placeholder.'" target="_blank">'.$placeholder.'</a>';
            $html = preg_replace($pattern, $replacement, $html, 1);
        }

        return $html;
    }


}