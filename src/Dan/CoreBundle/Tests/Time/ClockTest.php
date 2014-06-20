<?php

namespace Dan\CoreBundle\Tests\Time;

use Dan\CoreBundle\Test\WebTestCase;
use Dan\CoreBundle\Time\Clock;

class ClockTest extends WebTestCase
{
    public function getFixturesToLoad()
    {
        return array();
    }
    
    public function test_now()
    {
        $clock = new Clock();
        $now = new \DateTime();
        $clockNow = $clock->now();
        $this->assertGreaterThanOrEqual($now->format('Y-m-d H:i:s'), $clockNow->format('Y-m-d H:i:s'));
        $now->modify('+1 second');
        $this->assertLessThanOrEqual($now->format('Y-m-d H:i:s'), $clockNow->format('Y-m-d H:i:s'));
    }
    
    public function test_getDateTime()
    {
        $clock = new Clock();
        $date = new \DateTime('-1 month');
        $clockDate = $clock->getDateTime('-1 month');
        $this->assertGreaterThanOrEqual($date->format('Y-m-d H:i:s'), $clockDate->format('Y-m-d H:i:s'));
        $date->modify('+1 second');
        $this->assertLessThanOrEqual($date->format('Y-m-d H:i:s'), $clockDate->format('Y-m-d H:i:s'));
    }
}