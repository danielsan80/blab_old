<?php
namespace Dan\Plugin\DiaryBundle\Parser\Parser;
use Dan\Plugin\DiaryBundle\Parser\Parser;
use Dan\Plugin\DiaryBundle\Parser\Step;

class DefaultParser extends Parser
{
    protected function setup()
    {
        $this->addStep(new Step\SetTokens());
        $this->addStep(new Step\RemoveEmptyContentTokens());
        $this->addStep(new Step\ReadDates());
        $this->addStep(new Step\ReadProjects('dates.*'));
        $this->addStep(new Step\ReadContent('dates.*.projects.*'));
    }
    
}