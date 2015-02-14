<?php
namespace Dan\Plugin\DiaryBundle\Parser;

class Lexer
{
    protected $terminals;
            
    public function __construct()
    {
        $this->terminals = array();
    }


    public function run($content)
    {
        $tokens = array();
        $offset = 0;
        $length = strlen($content);
        
//        $i=0;
        while ($offset < $length) {
//            if ($i++>10) {
//                break;
//            }
            $result = $this->match($content, $offset);
            if ($result === false) {
                throw new Exception('Unable to parse the given content');
            }
            $tokens[] = $result;
            $offset += strlen($result['match']);            
        }
        
        
        return $tokens;
        
    }
    
    protected function match($content, $offset)
    {
        $string = substr($content, $offset);
        
        foreach($this->terminals as $terminal) {
            if ($token = $terminal->match($string)) {
                return $token;
            }
        }
        
        
        $inc = null;
        foreach($this->terminals as $terminal) {            
            if ($token = $terminal->findIn($string)) {
                if (!$inc) {
                    $inc = $token['position'];
                } elseif ($token['position'] < $inc) {
                    $inc = $token['position'];
                }
            }
        }
        
        $token = array(
            'match' => $inc?substr($string, 0, $inc):substr($string, 0),
            'token' => 'T_CONTENT',
        );
        
        $token['data'] = $token['match'];
        
        return $token;
        
    }
    
    public function addTerminal(LexerTerminalInterface $terminal)
    {
        $this->terminals[] = $terminal;
    }
    
   
    
}