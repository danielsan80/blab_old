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
        $lines = explode("\n", $content);
        
        $tokens = array();
        
        foreach($lines as $i => $line) {
            $offset = 0;
            $length = strlen($line);

            while ($offset < $length) {
                $result = $this->match($line, $offset);
                if ($result === false) {
                    throw new Exception('Unable to parse the given content');
                }
                $tokens[] = $result;
                $offset += strlen($result['match']);            
            }
            if ($i+1 < count($lines)) {
                $tokens[] = $this->getNewLineToken();
            }
        }
        
        
        return $tokens;
        
    }
    
    protected function match($line, $offset)
    {
        $string = substr($line, $offset);
        
        foreach($this->terminals as $terminal) {
            
            if ($terminal->getOption('must_be_at_start_of_line', false) && $offset>0) {
                continue;
            }
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
        
        $token = $this->getContentToken($inc?substr($string, 0, $inc):substr($string, 0));
        
        return $token;
        
    }
    
    public function addTerminal(LexerTerminalInterface $terminal)
    {
        $this->terminals[] = $terminal;
    }
    
    private function getNewLineToken()
    {
        return array(
            'match' => "\n",
            'token' => 'T_NEWLINE',
            'data' => "\n",
        );
    }
    
    private function getContentToken($string)
    {
        return array(
            'match' => $string,
            'token' => 'T_CONTENT',
            'data' => $string,
        );
    }
    
}