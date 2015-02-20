<?php
namespace Dan\Plugin\DiaryBundle\Parser\Step;

use Dan\Plugin\DiaryBundle\Parser\ParserStep;

class ReadContent extends ParserStep
{
    protected function run($path)
    {
        
        $tokens = $this->parser->getProperty($path.'._tokens', array());
        
        $contents = array();
        foreach($tokens as $i => $token) {
            if ($token['token'] == 'T_CONTENT' || $token['token'] == 'T_NEWLINE') {
                $contents[] = $token['data'];
                unset($tokens[$i]);
            } else {
                $contents[] = $token['match'];
                unset($tokens[$i]);
            }
        }
        
        if ($tokens) {
            $this->parser->setProperty($path.'._tokens', array_values($tokens));
        } else {
            $this->parser->unsetProperty($path.'._tokens');
        }
        
        if ($content = trim(implode('',$contents))) {
            $this->parser->setProperty($path.'.content', $content);
        }
    }
}