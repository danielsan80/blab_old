<?php

namespace Dan\Plugin\DiaryBundle\Tests\Parser\LexerTerminal;

use Dan\CoreBundle\Test\WebTestCase;
use Dan\Plugin\DiaryBundle\Parser\Parser;
use Dan\Plugin\DiaryBundle\Parser\DefaultParser;
use Symfony\Component\Yaml\Yaml;

class DateTest extends WebTestCase
{
    public function getFixturesToLoad()
    {
        return array();
    }
    
    public function provider()
    {
        return array(
            array('lun 16 marzo 2015', true, '2015-03-16'),
            array('LUN 16 marzo 2015', true, '2015-03-16'),
            array('lunedì 16 marzo 2015', true, '2015-03-16'),
            array('lun 16 mar 2015', true, '2015-03-16'),
            array('lun 16 Mar 2015', true, '2015-03-16'),
            array('lun 16 MARZO 2015', true, '2015-03-16'),
            array('16 MARZO 2015', true, '2015-03-16'),
            
            array('mar 16 MARZO 2015', false),
            array('lunedì 54 marzo 2015', false),
            array('aaa 16 MARZO 2015', false),
        );
    }



    /**
     * @dataProvider provider
     */
    public function testMatch($string, $match, $data = null)
    {
        $terminal = new \Dan\Plugin\DiaryBundle\Parser\LexerTerminal\Date();
        $expected = array(
            'match' => $string,
            'token' => 'T_DATE',
            'data' => '2015-03-16',
        );
        
        $token = $terminal->match($string);
        
        
        if ($match) {
            unset($token['matches']);
            $this->assertEquals($expected, $token);
        } else {
            $this->assertFalse($token);
        }
    }
    
}