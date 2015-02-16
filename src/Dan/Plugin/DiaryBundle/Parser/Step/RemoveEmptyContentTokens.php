<?php
namespace Dan\Plugin\DiaryBundle\Parser\Step;

use Dan\Plugin\DiaryBundle\Parser\ParserStep;

class RemoveEmptyContentTokens extends ParserStep
{
    
    
    protected function run($path)
    {
        
        $tokens = $this->parser->getProperty($path.'._tokens', array());
        
        foreach($tokens as $i => $token) {
            if ($token['token'] == 'T_CONTENT') {
                if (!trim($token['match'])) {
                    unset($tokens[$i]);
                }
            }
        }
        
        $this->parser->setProperty($path.'._tokens', array_values($tokens));
    }
}