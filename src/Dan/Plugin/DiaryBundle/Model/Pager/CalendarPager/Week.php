<?php

namespace Dan\Plugin\DiaryBundle\Model\Pager\CalendarPager;

class Week
{
    private $month;
    private $days;
    
    public function __construct(\DateTime $date, $month=null)
    {
        $this->setMonth($month);
        
        $monday = clone $date;
        $sunday = clone $date;
        
        if ($monday->format('D')!='Mon') {
            $monday->modify('last monday');
        }        
        if ($sunday->format('D')!='Sun') {
            $sunday->modify('next sunday');
        }

        $currentDay = clone $monday;
        
        $this->days = [];
        while($currentDay <= $sunday) {
            $this->days[] = new Day($currentDay, $month);
            $currentDay->modify('+1 day');
        }
        
    }
    
    public function setMonth($month = null)
    {
        $helper = new Helper();
        $this->month = $helper->getDateFromMonth($month);
    }
    
    public function getNotInMonthDays()
    {
        $days = [];
        
        foreach($this->days as $day) {
            if (!$day->isInMonth()) {
                $days[] = $day;
            }
        }
        
        return $days;        
    }
    
    public function getInMonthDays()
    {
        $days = [];
        
        foreach($this->days as $day) {
            if ($day->isInMonth()) {
                $days[] = $day;
            }
        }
        
        return $days;        
    }
    
    public function getPreviousMonthDays()
    {
        $days = [];
        
        foreach($this->days as $day) {
            if ($day->isBeforeMonth()) {
                $days[] = $day;
            }
        }
        
        return $days;        
    }
    
    public function getNextMonthDays()
    {
        $days = [];
        
        foreach($this->days as $day) {
            if ($day->isAfterMonth()) {
                $days[] = $day;
            }
        }
        
        return $days;   
    }
    
    public function getDays()
    {
        return $this->days;
    }
    
    public function addElement($el, $date) {
        foreach($this->days as $day) {
            $day->addElement($el, $date);
        }
    }
}
