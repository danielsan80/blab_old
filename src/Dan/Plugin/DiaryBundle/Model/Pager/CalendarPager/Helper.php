<?php

namespace Dan\Plugin\DiaryBundle\Model\Pager\CalendarPager;

class Helper
{
    public function getDateFromMonth($month=null)
    {
        
        if (is_null($month)) {
            $month = new \DateTime();
            
            $month->modify('first day of this month');
            return $month;
        }
        
        if (is_object($month) && is_a($month,'DateTime')) {
            $month = clone $month;
            $month->modify('first day of this month');
            return $month;
        }
        
        if (!is_string($month)) {
            throw new \Exception('The month must be a string with format "yyyy-mm" ');
        }
        
        if (!preg_match('/^(?P<year>\d{4})-(?P<month>\d{2})$/', $month, $matches)) {
            throw new \Exception('The month format is "yyyy-mm" ');
        }
        
        return new \DateTime($month);
    }
}
