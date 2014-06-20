<?php

namespace Dan\CoreBundle\Tests\Test\Time;

use Dan\CoreBundle\Test\WebTestCase;
use Dan\CoreBundle\Test\Time\Clock;

class ClockTest extends WebTestCase
{
    public function getFixturesToLoad()
    {
        return array();
    }
    
    public function test_now()
    {
        $clock = new Clock(new \DateTime('2012-12-21 00:00:00'));
        $clockNow = $clock->now();
        $now = new \DateTime('2012-12-21 00:00:00');
        $this->assertEquals($now->format('Y-m-d H:i:s'), $clockNow->format('Y-m-d H:i:s'));
    }
    
    public function test_getDateTime()
    {
        $now = new \DateTime('2012-12-21 00:00:00');
        $clock = new Clock($now);
        $date = new \DateTime('2012-12-21 00:00:00 -1 month');
        $clockDate = $clock->getDateTime('-1 month');
        $this->assertEquals($date->format('Y-m-d H:i:s'), $clockDate->format('Y-m-d H:i:s'));
    }
        
}