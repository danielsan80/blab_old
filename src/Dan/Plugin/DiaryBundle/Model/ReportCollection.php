<?php

namespace Dan\Plugin\DiaryBundle\Model;

use Dan\Plugin\DiaryBundle\Analysis\Helper;

class ReportCollection extends BaseReportCollection
{
    private $moneyPerHour;
    private $moneyPerDay;
    private $hoursPerDay = 7;
    private $currency = 'EUR';

    public function setMoneyPerHour($moneyPerHour)
    {
        $this->moneyPerHour = $moneyPerHour;
        return $this;
    }
    public function getMoneyPerHour()
    {
        if ($this->parent) {
            return $this->parent->getMoneyPerHour();
        }
        return $this->moneyPerHour;
    }

    public function setMoneyPerDay($moneyPerDay)
    {
        $this->moneyPerDay = $moneyPerDay;
        return $this;
    }
    public function getMoneyPerDay()
    {
        if ($this->parent) {
            return $this->parent->getMoneyPerDay();
        }
        return $this->moneyPerDay;
    }

    public function setCurrency($currency)
    {
        $this->currency = $currency;
        return $this;
    }
    public function getCurrency()
    {
        if ($this->parent) {
            return $this->parent->getCurrency();
        }
        return $this->Currency;
    }

    public function setHoursPerDay($hoursPerDay)
    {
        $this->hoursPerDay = $hoursPerDay;
        return $this;
    }
    public function getHoursPerDay()
    {
        if ($this->parent) {
            return $this->parent->getHoursPerDay();
        }
        return $this->hoursPerDay;
    }

    public function getSeconds()
    {

        $class = get_class($this);
        $helper = $this->getHelper();
        $seconds = 0;
        foreach($this->reports as $report) {
            if ($report instanceof $class ) {
                $seconds += $report->getSeconds();
            } else {
                $seconds += $helper->getTotalTime($report);
            }
        }
        return $seconds;
    }

    public function getHours()
    {
        return $this->getHelper()->getAsHours($this->getSeconds());
    }

    public function getDays()
    {
        return $this->getHoursPerDay() ? $this->getHours() / $this->getHoursPerDay() : null;
    }

    public function getMoney()
    {
        $hours = $this->getHours();
        $days = $this->getDays();

        $moneyPerHour = $this->getMoneyPerHour();
        $moneyPerDay = $this->getMoneyPerDay();

        $money = null;
        if ($moneyPerDay && $days) {
            $money = $days * $moneyPerDay;
        }
        if ($moneyPerHour && $hours) {
            $money = $hours * $moneyPerHour;
        }

        return $money;
    }

    public function getTasks()
    {
        $class = get_class($this);

        $helper = $this->getHelper();

        $tasks = array();
        foreach($this->reports as $report) {
            if ($report instanceof $class ) {
                $tasks = array_merge($tasks, $report->getTasks());
            } else {
                $tasks = array_merge($tasks, $helper->getTasks($report));
            }
        }

        return $tasks;
    }

    public function getDate()
    {
        $dates = $this->getDates();
        if (!$dates) {
            return null;
        }
        if (count($dates)>1) {
            return $dates;
        }
        foreach($dates as $date) {
            return $date;
        }
    }
    
    public function getDates()
    {
        $class = get_class($this);

        $helper = $this->getHelper();

        $dates = array();
        foreach($this->reports as $report) {
            if ($report instanceof $class ) {
                $dates = array_merge($dates, $report->getDates());
            } else {
                $date = $helper->getDate($report);
                $dates = array_merge($dates, array($date->format('Y-m-d') => $date) );
            }
        }

        return $dates;
    }

    public function getTasksAsHtml()
    {
        $tasks = $this->getTasks();
        foreach($tasks as $i => $task) {
            $tasks[$i] = $helper->getAsHtml($task);
        }

        return $tasks;
    }
}