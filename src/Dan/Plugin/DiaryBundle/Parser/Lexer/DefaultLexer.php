<?php
namespace Dan\Plugin\DiaryBundle\Parser\Lexer;

use Dan\Plugin\DiaryBundle\Parser\Lexer;
use Dan\Plugin\DiaryBundle\Parser\LexerTerminal;

class DefaultLexer extends Lexer
{
    
    public function __construct()
    {
        parent::__construct();
        
        $this->addTerminal(new LexerTerminal\Date());
        $this->addTerminal(new LexerTerminal\Project());
        $this->addTerminal(new LexerTerminal\TimeRange());
        $this->addTerminal(new LexerTerminal\TimeMod());
        $this->addTerminal(new LexerTerminal\Task());
        $this->addTerminal(new LexerTerminal\Tags());
    }
}