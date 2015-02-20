<?php
namespace Dan\Plugin\DiaryBundle\Parser\LexerTerminal;

use Dan\Plugin\DiaryBundle\Parser\Lexer;
use Dan\Plugin\DiaryBundle\Parser\LexerTerminal;
use Dan\Plugin\DiaryBundle\Parser\LexerTerminalInterface;

class Tags implements LexerTerminalInterface
{
    
    private $lexerTerminal;
    
    public function __construct()
    {
        $this->lexerTerminal = new LexerTerminal();
        $this->lexerTerminal->setName('T_TAGS');
        $this->lexerTerminal->addPattern('(tag[s]?|TAG[S]?|Tag[s]?): (?P<tags>[^\n]+)');
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
            $token['data'] = $matches['tags'];
        }
        return $token;
    }    
    
}