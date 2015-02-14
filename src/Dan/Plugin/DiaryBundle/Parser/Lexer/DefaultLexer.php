<?php
namespace Dan\Plugin\DiaryBundle\Parser\Lexer;

use Dan\Plugin\DiaryBundle\Parser\Lexer;
use Dan\Plugin\DiaryBundle\Parser\LexerTerminal\Date;
use Dan\Plugin\DiaryBundle\Parser\LexerTerminal\Project;

class DefaultLexer extends Lexer
{
    
    public function __construct()
    {
        parent::__construct();
        
        $this->addTerminal(new Date());
        $this->addTerminal(new Project());
    }
}