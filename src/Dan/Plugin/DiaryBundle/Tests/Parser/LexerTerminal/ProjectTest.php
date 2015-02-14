<?php

namespace Dan\Plugin\DiaryBundle\Tests\Parser\LexerTerminal;

use Dan\CoreBundle\Test\WebTestCase;
use Dan\Plugin\DiaryBundle\Parser\Parser;
use Dan\Plugin\DiaryBundle\Parser\DefaultParser;
use Symfony\Component\Yaml\Yaml;

class ProjectTest extends WebTestCase
{
    public function getFixturesToLoad()
    {
        return array();
    }
    
    public function provider()
    {
        return array(
            array('[project1]', true, 'project1'),
            array('[project 1]', true, 'project 1'),
            array('project1', false),
        );
    }



    /**
     * @dataProvider provider
     */
    public function testMatch($string, $match, $data = null)
    {
        $terminal = new \Dan\Plugin\DiaryBundle\Parser\LexerTerminal\Project();
        $expected = array(
            'match' => $string,
            'token' => 'T_PROJECT',
            'data' => $data,
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