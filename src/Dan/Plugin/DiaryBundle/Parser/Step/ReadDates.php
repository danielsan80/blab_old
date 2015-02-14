<?php
namespace Dan\Plugin\DiaryBundle\Parser\Step;

use Dan\Plugin\DiaryBundle\Parser\ParserStep;

class ReadDates extends ParserStep
{
    protected function run($path)
    {
        
        $tokens = $this->parser->getProperty($path.'._tokens');
        
        $dates = array();
        $date = null;
        foreach($tokens as $i => $token) {
            if ($token['token'] == 'T_DATE') {
                if ($date) {
                    $dates[] = $date;
                }
                $date = array(
                    'date' => $token['data'],                   
                    '_tokens' => array(),
                );
                unset($tokens[$i]);
            } elseif ($date) {
                $date['_tokens'][] = $token;
                unset($tokens[$i]);
            }
        }
        if ($date) {
            $dates[] = $date;
        }
        
        if ($tokens) {
            $this->parser->setProperty($path.'._tokens', array_values($tokens));
        } else {
            $this->parser->unsetProperty($path.'._tokens');
        }
        $this->parser->setProperty($path.'.dates', $dates);
    }
}