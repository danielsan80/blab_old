<?php
namespace Dan\Plugin\DiaryBundle\Parser\LexerTerminal;

use Dan\Plugin\DiaryBundle\Parser\Lexer;
use Dan\Plugin\DiaryBundle\Parser\LexerTerminal;
use Dan\Plugin\DiaryBundle\Parser\LexerTerminalInterface;

class Date implements LexerTerminalInterface
{
    
    private $lexerTerminal;
    
    public function __construct()
    {
        $this->lexerTerminal = new LexerTerminal();
        $this->lexerTerminal->setName('T_DATE');
        $this->lexerTerminal->addPattern('((?P<dow>'.$this->getDowsPattern().') )?(?P<day>\d{2}) (?P<month>'.$this->getMonthsPattern().') (?P<year>\d{4})', 'i');
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
            try {
                $date = new \DateTime($matches['year'].'-'.$this->getMonthAsNumber($matches['month']).'-'.$matches['day']);
            } catch (\Exception $e) {
                return false;
            }
            
            if ($matches['dow'] && ($date->format('w')!=$this->getDowAsNumber($matches['dow']))) {
                return false;
            }
            
            $token['data'] = $date->format('Y-m-d');
        }
        return $token;
    }
    
    
    private function getMonths()
    {
        return array('gennaio', 'febbraio', 'marzo', 'aprile', 'maggio', 'giugno', 'luglio', 'agosto', 'settembre', 'ottobre', 'novembre', 'dicembre');
    }
    
    private function getMonthsPattern()
    {
        $months = $this->getMonths();
        $_months = $months;
        foreach($months as $month) {
            $_months[] = substr($month, 0,3);
        }
        return implode('|', $_months);
    }
    
    private function getDows()
    {
        return array('domenica', 'lunedì', 'martedì', 'mercoledì', 'giovedì', 'venerdì', 'sabato');
    }
    
    private function getDowsPattern()
    {
        $dows = $this->getDows();
        $_dows = $dows;
        foreach($dows as $dow) {
            $_dows[] = substr($dow, 0,3);
        }
        return implode('|', $_dows);
    }
    
    public function getMonthAsNumber($value)
    {
        $value = strtolower($value);
        
        $months = array_flip($this->getMonths());
        if (isset($months[$value])) {
            return str_pad($months[$value]+1, 2, '0', STR_PAD_LEFT);
        }
        
        $months = $this->getMonths();
        foreach($months as $i => $month){
            $months[$i] = substr($month,0,3);
        }
        $months = array_flip($months);
        
        if (isset($months[$value])) {
            return str_pad($months[$value]+1, 2, '0', STR_PAD_LEFT);
        }

        return $value;
    }
    
    public function getDowAsNumber($value)
    {
        $value = strtolower($value);
        
        $dows = array_flip($this->getDows());
        if (isset($dows[$value])) {
            return $dows[$value];
        }
        
        $dows = $this->getDows();
        foreach($dows as $i => $dow){
            $dows[$i] = substr($dow,0,3);
        }
        $dows = array_flip($dows);
        
        if (isset($dows[$value])) {
            return $dows[$value];
        }

        return $value;
    }
    
}