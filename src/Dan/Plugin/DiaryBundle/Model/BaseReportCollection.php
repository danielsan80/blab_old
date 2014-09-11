<?php

namespace Dan\Plugin\DiaryBundle\Model;

use Dan\Plugin\DiaryBundle\Analysis\Helper;

class BaseReportCollection implements \ArrayAccess
{
    protected $parent;
    protected $date;
    protected $reports;
    protected $pathSeparator = '.';

    private $helper;

    public function __construct(BaseReportCollection $parent = null)
    {
        $this->parent = $parent;
        $this->reports = array();
    }

    public function setHelper(Helper $helper)
    {
        $this->helper = $helper;
        return $this;
    }
    public function getHelper()
    {
        if ($this->parent) {
            return $this->parent->getHelper();
        }
        if (!$this->helper) {
            $this->helper = new Helper();
        }
        return $this->helper;
    }

    public function setReports($reports)
    {
        $this->reports = $report;
        return $this;
    }
    public function getReports()
    {
        ksort($this->reports);
        return $this->reports;
    }
    public function addReport($report, $path=null)
    {
        
        if (!$path) {
            $this->reports[] = $report;
            return $this;
        }
        $separator = $this->pathSeparator;

        $path = explode($separator, $path, 2);
        $key = $path[0];
        $subpath = isset($path[1])?$path[1]:null;
        if (!isset($this->reports[$key])) {
            $this->reports[$key] = $this->createReportCollection();
        }
        $this->reports[$key]->addReport($report, $subpath, $separator);
        return $this;
    }
    
    private function createReportCollection()
    {
        $collection = new ReportCollection($this);
        return $collection;
    }

    public function offsetSet($offset, $value) {
        throw new \Exception('Method not allowed');
    }
    public function offsetExists($offset) {
        return isset($this->reports[$offset]);
    }
    public function offsetUnset($offset) {
        throw new \Exception('Method not allowed');
    }
    public function offsetGet($offset) {
        return isset($this->reports[$offset]) ? $this->reports[$offset] : null;
    }
}