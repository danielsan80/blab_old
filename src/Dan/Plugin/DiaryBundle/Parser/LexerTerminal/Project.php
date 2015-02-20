<?php
namespace Dan\Plugin\DiaryBundle\Parser\LexerTerminal;

use Dan\Plugin\DiaryBundle\Parser\Lexer;
use Dan\Plugin\DiaryBundle\Parser\LexerTerminal;
use Dan\Plugin\DiaryBundle\Parser\LexerTerminalInterface;

class Project implements LexerTerminalInterface
{
    
    private $lexerTerminal;
    
    public function __construct()
    {
        $this->lexerTerminal = new LexerTerminal();
        $this->lexerTerminal->setName('T_PROJECT');
        $this->lexerTerminal->addPattern('\[(?P<project>[\w ]+)\]');
        $this->lexerTerminal->setOption('must_be_at_start_of_line', true);
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
            $token['data'] = $matches['project'];
        }
        return $token;
    }
    
}