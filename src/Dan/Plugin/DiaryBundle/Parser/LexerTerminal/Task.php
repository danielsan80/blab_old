<?php
namespace Dan\Plugin\DiaryBundle\Parser\LexerTerminal;

use Dan\Plugin\DiaryBundle\Parser\Lexer;
use Dan\Plugin\DiaryBundle\Parser\LexerTerminal;
use Dan\Plugin\DiaryBundle\Parser\LexerTerminalInterface;

class Task implements LexerTerminalInterface
{
    
    private $lexerTerminal;
    
    public function __construct()
    {
        $this->lexerTerminal = new LexerTerminal();
        $this->lexerTerminal->setName('T_TASK');
        $this->lexerTerminal->addPattern('(task|TASK|Task): (?P<task>[^\n]+)');
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
            $token['data'] = $matches['task'];
        }
        return $token;
    }    
    
}