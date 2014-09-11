<?php

namespace Dan\Plugin\DiaryBundle\Tests\Model;

use Dan\CoreBundle\Test\WebTestCase;
use Dan\Plugin\DiaryBundle\Entity\Report;
use Dan\Plugin\DiaryBundle\Model\ReportCollection;

class ReportCollectionTest extends WebTestCase
{
    public function getFixturesToLoad()
    {
        return array();
    }
    
    public function test_simpleInsert()
    {
        $report = new Report();
        $collection = new ReportCollection();
        $collection->addReport($report);

        $this->assertEquals(array($report), $collection->getReports());

    }

    public function test_insert()
    {
        $report = new Report();
        $collection = new ReportCollection();
        $collection->addReport($report,'a.b');

        $this->assertCount(1, $collection->getReports());
        $this->assertCount(1, $collection['a']->getReports());
        $this->assertCount(1, $collection['a']['b']->getReports());

        $this->assertEquals(array($report), $collection['a']['b']->getReports());

        $collection->addReport($report,'a.c');

        $this->assertCount(1, $collection->getReports());
        $this->assertCount(2, $collection['a']->getReports());
        $this->assertCount(1, $collection['a']['b']->getReports());
        $this->assertCount(1, $collection['a']['c']->getReports());


    }

    public function test_configAvailability()
    {
        $report = new Report();
        $collection = new ReportCollection();
        $collection->setMoneyPerDay(50);
        $collection->addReport($report,'a.b.c');

        $this->assertEquals(50, $collection['a']['b']['c']->getMoneyPerDay());

    }
    
}