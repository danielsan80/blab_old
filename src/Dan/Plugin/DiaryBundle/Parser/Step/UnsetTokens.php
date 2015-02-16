<?php
namespace Dan\Plugin\DiaryBundle\Parser\Step;

use Dan\Plugin\DiaryBundle\Parser\ParserStep;

class UnsetTokens extends ParserStep
{
    
    protected function run($path)
    {
        $this->parser->unsetProperty($path.'._tokens');
    }
}