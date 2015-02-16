<?php
namespace Dan\Plugin\DiaryBundle\Parser\Step;

use Dan\Plugin\DiaryBundle\Parser\ParserStep;

class ReadTags extends ParserStep
{
    protected function run($path)
    {
        
        $tokens = $this->parser->getProperty($path.'._tokens', array());
        
        $tags = array();
        foreach($tokens as $i => $token) {
            if ($token['token'] == 'T_TAGS') {
                $tags = array_merge($tags, explode(',', $token['data']));
//                unset($tokens[$i]);
            }
        }
        
        foreach($tags as $i => $tag) {
            $tags[$i] = trim($tag);
        }
        $tags = array_unique($tags);
        
        
        if ($tokens) {
            $this->parser->setProperty($path.'._tokens', array_values($tokens));
        } else {
            $this->parser->unsetProperty($path.'._tokens');
        }
        if ($tags) {
            $this->parser->setProperty($path.'.tags', $tags);
        }
    }
}