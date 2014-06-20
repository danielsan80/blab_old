<?php

namespace Dan\CoreBundle\Test\Time;

use Dan\CoreBundle\Time\Clock as BaseClock;

class Clock extends BaseClock
{
    private $now;
    
    public function __construct(\DateTime $now=null)
    {
        $this->now = $now;
    }
    
    public function getDateTime($str=null)
    {
        if (!$this->now) {
            return parent::getDateTime($str);
        }
        $now = clone $this->now;
        try {
            return new \DateTime($now->format('Y-m-d H:i:s').' '.$str);
            
        } catch (\Exception $e) {
            
            return new \DateTime($str);
        }
    }
    
}