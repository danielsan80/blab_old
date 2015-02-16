<?php
namespace Dan\Plugin\DiaryBundle\Parser\Step;

use Dan\Plugin\DiaryBundle\Parser\ParserStep;

class ReadTasks extends ParserStep
{
    protected function run($path)
    {
        
        $tokens = $this->parser->getProperty($path.'._tokens', array());
        
        $tasks = array();
        foreach($tokens as $i => $token) {
            if ($token['token'] == 'T_TASK') {
                $tasks[] = trim($token['data']);
//                unset($tokens[$i]);
            }
        }
        
        if ($tokens) {
            $this->parser->setProperty($path.'._tokens', array_values($tokens));
        } else {
            $this->parser->unsetProperty($path.'._tokens');
        }
        if ($tasks) {
            $this->parser->setProperty($path.'.tasks', $tasks);
        }
    }
}