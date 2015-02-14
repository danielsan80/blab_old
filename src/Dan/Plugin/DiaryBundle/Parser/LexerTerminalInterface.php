<?php
namespace Dan\Plugin\DiaryBundle\Parser;

interface LexerTerminalInterface
{
    public function match($string);
    public function findIn($string);
}