<?php
namespace Dan\Plugin\DiaryBundle\Parser\Decomposer;

use Dan\Plugin\DiaryBundle\Parser\DecomposerInterface;

class DateList implements DecomposerInterface
{
    
    
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
        return array('lunedì', 'martedì', 'mercoledì', 'giovedì', 'venerdì', 'sabato', 'domenica');
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
        
        $months = array_flip($this->getMonths());
        foreach($months as $i => $month){
            $months[$i] = substr($month,0,3);
        }
        $months = array_flip($months);
        
        if (isset($months[$value])) {
            return str_pad($months[$value]+1, 2, '0', STR_PAD_LEFT);
        }

        return $value;
    }
    
    public function decompose($content)
    {
        
        $patterns = array(
            '/(?P<dow>('.$this->getDowsPattern().') )?(?P<day>\d{2}) (?P<month>'.$this->getMonthsPattern().') (?P<year>\d{4})/i',
        );
        
        $dates = array();
        $placeholders = array();
        
        while (true) {
            $matches = null;
            foreach($patterns as $pattern) {
                if (preg_match($pattern, $content, $matches)) {
                    break;
                }                
            }
            if (!$matches) {
                break;
            }
            
            $placeholder = array(
                'value' => $matches[0],
                'data' => $matches['year'].'-'.$this->getMonthAsNumber($matches['month']).'-'.$matches['day'],
                'title' => 'date'
            );
            $placeholders[] = $placeholder; 
            
            $value = strtr(preg_quote($placeholder['value']), array('/' => '\\/'));
            $content = preg_replace('/'.$value.'/', '{{'.(count($placeholders)-1).'}}', $content, 1);
        }
        
        $pattern = '/{{(?P<i>\d+)}}/';
        $dateContents = preg_split($pattern, $content);
        
        foreach($placeholders as $i => $placeholder) {
            $dates[] = array(
                'date' => $placeholder['data'],
                'content' => trim($dateContents[$i+1]),
            );
        }
        
        return array(
            'dates' => $dates,
            'content' => $content,
            'placeholders' => $placeholders,
        );
        
    }
}