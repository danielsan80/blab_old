<?php
namespace Dan\Plugin\DiaryBundle\Regexp;

class Transformer
{
    public function month($value)
    {
        $value = strtolower($value);
        
        $months = array_flip(array('gennaio', 'febbraio', 'marzo', 'aprile', 'maggio', 'giugno', 'luglio', 'agosto', 'settembre', 'ottobre', 'novembre', 'dicembre'));
        if (isset($months[$value])) {
            return str_pad($months[$value]+1, 2, '0', STR_PAD_LEFT);
        }

        $months = array_flip(array('gen', 'feb', 'mar', 'apr', 'mag', 'giu', 'lug', 'ago', 'set', 'ott', 'nov', 'dic'));
        if (isset($months[$value])) {
            return str_pad($months[$value]+1, 2, '0', STR_PAD_LEFT);
        }

        return $value;
    }
}