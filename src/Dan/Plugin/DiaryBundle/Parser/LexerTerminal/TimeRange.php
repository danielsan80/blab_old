<?php
namespace Dan\Plugin\DiaryBundle\Parser\LexerTerminal;

use Dan\Plugin\DiaryBundle\Parser\Lexer;
use Dan\Plugin\DiaryBundle\Parser\LexerTerminal;
use Dan\Plugin\DiaryBundle\Parser\LexerTerminalInterface;

class TimeRange implements LexerTerminalInterface
{
    
    private $lexerTerminal;
    
    public function __construct()
    {
        $this->lexerTerminal = new LexerTerminal();
        $this->lexerTerminal->setName('T_TIME_RANGE');
        $this->lexerTerminal->addPattern('(?P<from>\d{1,2}\.\d{2})[ ]?-[ ]?(?P<to>\d{1,2}\.\d{2})');
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
    
    public function getOption($key)
    {
        return $this->lexerTerminal->getOption($key);
    }
    
    private function afterMatch($token)
    {
        if ($token) {
            $matches = $token['matches'];
            $token['data'] = str_pad($matches['from'], 5, "0", STR_PAD_LEFT) .' - '. $matches['to'];
        }
        return $token;
    }    
    
}