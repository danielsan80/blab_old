<?php

namespace Dan\Plugin\DiaryBundle\Model;

use Dan\Plugin\DiaryBundle\Analysis\Helper;

class ReportCollection
{
    private $reports;
    private $moneyPerHour;
    private $moneyPerDay;
    private $hoursPerDay = 7;
    private $currency = 'EUR';

    private $helper;

    public function __construct()
    {
        $this->reports = array();
    }
    public function setHelper(Helper $helper)
    {
        $this->helper = $helper;
        return $this;
    }
    public function getHelper()
    {
        if (!$this->helper) {
            $this->helper = new Helper();
        }
        return $this->helper;
    }

    public function setReports($reports)
    {
        $this->reports = $report;
        return $this;
    }
    public function getReports()
    {
        return $this->reports;
    }
    public function addReport($report)
    {
        $this->reports[] = $report;
        return $this;
    }
    

    public function setMoneyPerHour($moneyPerHour)
    {
        $this->moneyPerHour = $moneyPerHour;
        return $this;
    }
    public function getMoneyPerHour()
    {
        return $this->moneyPerHour;
    }

    public function setMoneyPerDay($moneyPerDay)
    {
        $this->moneyPerDay = $moneyPerDay;
        return $this;
    }
    public function getMoneyPerDay()
    {
        return $this->moneyPerDay;
    }

    public function setCurrency($currency)
    {
        $this->currency = $currency;
        return $this;
    }
    public function getCurrency()
    {
        return $this->Currency;
    }

    public function setHoursPerDay($hoursPerDay)
    {
        $this->hoursPerDay = $hoursPerDay;
        return $this;
    }
    public function getHoursPerDay()
    {
        return $this->hoursPerDay;
    }

    public function getSeconds()
    {
        try {
            return $this->reports->getSeconds();
        } catch (\Exception $e) {}

        $helper = $this->getHelper();
        $seconds = 0;
        foreach($this->reports as $report) {
            $seconds += $helper->getTotalTime($report);
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
        $days = $this->getDay();

        $moneyPerHour = $this->getMoneyPerHour();
        $moneyPerDay = $this->getMoneyPerDay();

        $money = null;
        if ($moneyPerDay && $days) {
            $money = $days * $euroPerDay;
        }
        if ($moneyPerHour && $hours) {
            $money = $hours * $moneyPerHour;
        }

        return $money;
    }
}