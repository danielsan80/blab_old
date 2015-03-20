<?php

namespace Dan\Plugin\DiaryBundle\Model\Pager;

class CalendarPager
{
    private $elements;
    private $month;
    private $dateRetrieveCallback;
    private $idRetrieveCallback;

    public function __construct(array $elements)
    {
        $this->elements = $elements;
        $this->setMonth();
        
        $this->dateRetrieveCallback;
        
        $this->idRetrieveCallback = function(){
            return null;
        };
    }

    public function setMonth($month=null)
    {
        $helper = new CalendarPager\Helper();
        $this->month = $helper->getDateFromMonth($month);
    }
    
    public function getWeeks()
    {
        if (!$this->month) {
            throw new \Exception('You must set a month: "yyyy-mm" ');
        }
        
        if ($this->elements && !$this->dateRetrieveCallback) {
            throw new \Exception('You must set a callback to get the element date with setDateRetriveCallback()');
        }

        $weeks = $this->generateMonthWeeks($this->month);
        
        $getDate = $this->dateRetrieveCallback;
        $getId = $this->idRetrieveCallback;
        
        foreach($this->elements as $el) {
            foreach($weeks as $week) {
                $week->addElement($el, $getDate($el), $getId($el));
            }
        }
        
        return $weeks;
    }
    
    private function generateMonthWeeks($month)
    {
        $firstDay = clone $month;
        $lastDay = clone $month;
        
        $firstDay->modify('first day of this month');
        $lastDay->modify('last day of this month');
        
        $lastMonday = clone $firstDay;
        $nextSunday = clone $lastDay;
        
        if ($firstDay->format('D')!='Mon') {
            $lastMonday->modify('last monday');
        }
        if ($lastDay->format('D')!='Sun') {
            $nextSunday->modify('next sunday');
        }
        
        $currentMonday = clone $lastMonday;
        
        $weeks = [];
        while($currentMonday < $nextSunday) {
            $weeks[] = new CalendarPager\Week($currentMonday, $month);
            $currentMonday->modify('next monday');
        }
        
        return $weeks;
    }
    
    public function setDateRetrieveCallback(callable $callback)
    {
        $this->dateRetrieveCallback = $callback;
    }
    
    public function setIdRetrieveCallback(callable $callback)
    {
        $this->idRetrieveCallback = $callback;
    }
    
 }