<?php
namespace Dan\Plugin\DiaryBundle\Parser\LexerTerminal;

use Dan\Plugin\DiaryBundle\Parser\Lexer;
use Dan\Plugin\DiaryBundle\Parser\LexerTerminal;
use Dan\Plugin\DiaryBundle\Parser\LexerTerminalInterface;

class TimeMod implements LexerTerminalInterface
{
    
    private $lexerTerminal;
    
    public function __construct()
    {
        $this->lexerTerminal = new LexerTerminal();
        $this->lexerTerminal->setName('T_TIME_MOD');
        
        $this->lexerTerminal->addPattern('(?P<sign>[\+\-]{1})[ ]?(?P<num>\d{1,2})[ ]?(?P<unit>[hm])');
        $this->lexerTerminal->addPattern('(?P<sign>[\+\-]{1})[ ]?(?P<num>\d{1,2}\.\d{2})[ ]?(?P<unit>[h]?)');
    }
    
    public function match($string)
    {
        $token = $this->lexerTerminal->match($string);
        return $this->afterMatch($token);
    }
    
    public function findIn($string)
    {
        $token = $this->lexerTerminal->findIn($string);
        return $this->afterMatch($token);
    }  
    
    private function afterMatch($token)
    {
        if ($token) {
            $matches = $token['matches'];
            $token['data'] = $matches['sign'] . $matches['num'] . $matches['unit'];
        }
        return $token;
    }    
    
}