<?php

namespace Dan\Plugin\DiaryBundle\Model\Pager\CalendarPager;

class Day
{
    private $date;
    private $month;
    private $elements;
    
    public function __construct(\DateTime $date, $month=null)
    {
        $this->setMonth($month);
        $this->date = clone $date;
        $this->elements = [];
    }
    
    public function setMonth($month = null)
    {
        $helper = new Helper();
        $this->month = $helper->getDateFromMonth($month);
    }
    
    public function getDate()
    {
        return $this->date;
    }
    
    public function getElements()
    {
        return $this->elements;
    }
    
    
    public function addElement($element, $date = null, $id = null)
    {
        if ($date && $this->date != $date) {
            return;
        }
        
        if ($id) {
            $this->elements[$id] = $element;
        } else {
            $this->elements[] = $element;
        }
    }
    
    private function doIsInMonth($mode=null)
    {
        if (!in_array($mode, ['checkBefore', 'checkAfter', null])) {
            throw new \Exception('mode must be "checkBefore", "checkAfter" or null, "'.$mode.'" given');
        }
        
        $month = $this->month->format('Y-m');
        
        if ($mode=='checkBefore') {
            if ( $this->date->format('Y-m') < $month ) {
                return false;
            }
        } elseif ($mode=='checkAfter') {
            if ( $this->date->format('Y-m') > $month) {
                return false;
            }
        } elseif ($this->date->format('Y-m') != $month) {
            return false;
        }
        
        return true;        
    }
    
    public function isInMonth()
    {
        return $this->doIsInMonth();
    }
    public function isBeforeMonth()
    {
        return !$this->doIsInMonth('checkBefore');
    }
    public function isAfterMonth()
    {
        return !$this->doIsInMonth('checkAfter');
    }
    
    public function isPast()
    {
        if ($this->date < new \DateTime('today')) {
            return true;
        }
        return false;
    }
    
    public function isToday()
    {
        if ($this->date == new \DateTime('today')) {
            return true;
        }
        return false;
    }
}
