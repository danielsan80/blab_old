<?php
namespace Dan\Plugin\DiaryBundle\Parser\Step;

use Dan\Plugin\DiaryBundle\Parser\ParserStep;

class ReadTimeRanges extends ParserStep
{
    protected function run($path)
    {
        
        $tokens = $this->parser->getProperty($path.'._tokens', array());
        
        $ranges = array();
        foreach($tokens as $i => $token) {
            if ($token['token'] == 'T_TIME_RANGE') {
                $ranges[] = $token['data'];
//                unset($tokens[$i]);
            }
        }
        
        if ($tokens) {
            $this->parser->setProperty($path.'._tokens', array_values($tokens));
        } else {
            $this->parser->unsetProperty($path.'._tokens');
        }
        if ($ranges) {
            $this->parser->setProperty($path.'.time_ranges', $ranges);
        }
    }
}