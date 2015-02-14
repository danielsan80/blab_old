<?php
namespace Dan\Plugin\DiaryBundle\Parser\Step;

use Dan\Plugin\DiaryBundle\Parser\ParserStep;

class ReadProjects extends ParserStep
{
    protected function run($path)
    {
        
        $tokens = $this->parser->getProperty($path.'._tokens');
        
        $projects = array();
        $project = null;
        foreach($tokens as $i => $token) {
            if ($token['token'] == 'T_PROJECT') {
                if ($project) {
                    $projects[] = $project;
                }
                $project = array(
                    'project' => $token['data'],                   
                    '_tokens' => array(),
                );
                unset($tokens[$i]);
            } elseif ($project) {
                $project['_tokens'][] = $token;
                unset($tokens[$i]);
            }
        }
        if ($project) {
            $projects[] = $project;
        }
        
        if ($tokens) {
            $this->parser->setProperty($path.'._tokens', array_values($tokens));
        } else {
            $this->parser->unsetProperty($path.'._tokens');
        }
        $this->parser->setProperty($path.'.projects', $projects);
    }
}