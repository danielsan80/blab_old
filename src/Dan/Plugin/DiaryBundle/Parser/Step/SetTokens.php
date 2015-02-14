<?php
namespace Dan\Plugin\DiaryBundle\Parser\Step;

use Dan\Plugin\DiaryBundle\Parser\ParserStep;

class SetTokens extends ParserStep
{
    
    protected function run($path)
    {
        $this->parser->setProperty($path.'._tokens', $this->parser->getTokens());
    }
}