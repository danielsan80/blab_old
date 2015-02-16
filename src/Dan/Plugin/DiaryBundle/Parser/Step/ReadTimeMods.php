<?php
namespace Dan\Plugin\DiaryBundle\Parser\Step;

use Dan\Plugin\DiaryBundle\Parser\ParserStep;

class ReadTimeMods extends ParserStep
{
    protected function run($path)
    {
        
        $tokens = $this->parser->getProperty($path.'._tokens', array());
        
        $mods = array();
        foreach($tokens as $i => $token) {
            if ($token['token'] == 'T_TIME_MOD') {
                $mods[] = $token['data'];
//                unset($tokens[$i]);
            }
        }
        
        if ($tokens) {
            $this->parser->setProperty($path.'._tokens', array_values($tokens));
        } else {
            $this->parser->unsetProperty($path.'._tokens');
        }
        if ($mods) {
            $this->parser->setProperty($path.'.time_mods', $mods);
        }
    }
}