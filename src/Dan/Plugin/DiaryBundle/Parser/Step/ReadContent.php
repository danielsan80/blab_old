<?php
namespace Dan\Plugin\DiaryBundle\Parser\Step;

use Dan\Plugin\DiaryBundle\Parser\ParserStep;

class ReadContent extends ParserStep
{
    protected function run($path)
    {
        
        $tokens = $this->parser->getProperty($path.'._tokens');
        
        $contents = array();
        foreach($tokens as $i => $token) {
            if ($token['token'] == 'T_CONTENT') {
                $contents[] = $token['data'];
                unset($tokens[$i]);
            }
        }
        
        if ($tokens) {
            $this->parser->setProperty($path.'._tokens', array_values($tokens));
        } else {
            $this->parser->unsetProperty($path.'._tokens');
        }
        $this->parser->setProperty($path.'.content', trim(implode('',$contents)));
    }
}