<?php
namespace Dan\Plugin\DiaryBundle\Parser\Step;

use Dan\Plugin\DiaryBundle\Parser\ParserStep;

class ReadPlaceholders extends ParserStep
{
    protected function run($path)
    {
        
        $tokens = $this->parser->getProperty($path.'._tokens', array());
        
        $contents = array();
        $placeholders = array();
        foreach($tokens as $i => $token) {
            if ($token['token'] == 'T_CONTENT') {
                $contents[] = $token['data'];
            } else {
                $j = count($placeholders);
                $contents[] = '{{'.$j.'}}';
                $placeholders[] = array(
                    'value' => $token['match'],
                    'title' => $this->getTitleByToken($token['token']),
                );
            }
        }
        
        $this->parser->unsetProperty($path.'._tokens');
        $this->parser->setProperty($path.'.content', implode('',$contents));
        $this->parser->setProperty($path.'.placeholders', $placeholders);
    }
    
    protected function getTitleByToken($token)
    {
        return strtolower(strtr($token, array('T_' => '')));
    }
}