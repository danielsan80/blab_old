<?php
namespace Dan\CoreBundle\Time;

class Clock
{
    public function now()
    {
        return $this->getDateTime();
    }
    
    public function getDateTime($str=null)
    {
        return new \DateTime($str);
    }
}