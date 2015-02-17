<?php
namespace Dan\Plugin\DiaryBundle\Parser\Parser;
use Dan\Plugin\DiaryBundle\Parser\Parser;
use Dan\Plugin\DiaryBundle\Parser\Step;

class DefaultParser extends Parser
{
    
    protected function setup()
    {
        $this->addStep(new Step\SetTokens());
//        $this->addStep(new Step\RemoveEmptyContentTokens()); //WRONG
        
        $this->addStep(new Step\ReadDates());
        $this->addStep(new Step\UnsetTokens());
        
        $this->addStep(new Step\ReadProjects('properties.dates.*'));
        $this->addStep(new Step\UnsetTokens('properties.dates.*'));
        
        $this->addStep(new Step\ReadTimeRanges('properties.dates.*.projects.*'));
        $this->addStep(new Step\ReadTimeMods('properties.dates.*.projects.*'));
        $this->addStep(new Step\ReadTasks('properties.dates.*.projects.*'));
        $this->addStep(new Step\ReadTags('properties.dates.*.projects.*'));
        
        $this->addStep(new Step\ReadContent('properties.dates.*.projects.*'));
        $this->addStep(new Step\UnsetTokens('properties.dates.*.projects.*'));
        
        $this->addStep(new Step\SetTokens());
        $this->addStep(new Step\ReadPlaceholders());
    }
    
}