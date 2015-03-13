<?php

namespace Dan\Plugin\DiaryBundle\Tests\Model\Pager;

use Dan\CoreBundle\Test\WebTestCase;
use Dan\Plugin\DiaryBundle\Model\Pager\CalendarPager;

class CalendarPagerTest extends WebTestCase
{
    public function getFixturesToLoad()
    {
        return array();
    }
    
    public function testStandardMonth()
    {
        $pager = new CalendarPager([]);
        $pager->setMonth('2015-03');
        $weeks = $pager->getWeeks();
        $this->assertCount(6, $pager->getWeeks());
    }
    
    public function testMonthStartsWithMonday()
    {
        $pager = new CalendarPager([]);
        $pager->setMonth('2015-05');
        $weeks = $pager->getWeeks();
        $this->assertCount(5, $pager->getWeeks());
    }
    
    public function testMonthEndsWithSunday()
    {
        $pager = new CalendarPager([]);
        $pager->setMonth('2015-05');
        $weeks = $pager->getWeeks();
        $this->assertCount(5, $pager->getWeeks());
    }
}