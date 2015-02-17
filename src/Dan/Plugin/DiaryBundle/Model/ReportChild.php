<?php
namespace Dan\Plugin\DiaryBundle\Model;

use Dan\Plugin\DiaryBundle\Entity\Report;

class ReportChild
{
    private $parent;
    private $properties;
    private $content;
    
    public function setProperties($properties)
    {
        $this->properties = $properties;
    }
    
    public function getProperties()
    {
        return $this->properties;
    }
    public function setContent($content)
    {
        $this->content = $content;
    }
    
    public function getContent()
    {
        return $this->content;
    }
    
    public function setParent(Report $report)
    {
        $this->parent = $report;
    }
    
    public function getParent()
    {
        return $this->parent;
    }
    
}