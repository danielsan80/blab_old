<?php

namespace Dan\Plugin\DiaryBundle\Tests\Parser;

use Dan\CoreBundle\Test\WebTestCase;
use Dan\Plugin\DiaryBundle\Parser\Lexer\DefaultLexer;
use Symfony\Component\Yaml\Yaml;

class LexerTest extends WebTestCase
{
    public function getFixturesToLoad()
    {
        return array();
    }

    public function testDefaultLeser()
    {
        $kernel = $this->getContainer()->get('kernel');
        $filename = $kernel->locateResource('@DanPluginDiaryBundle/Test/providers/Lexer/DefaultLexer/content.txt');
        $content = file_get_contents($filename);
        
        $lexer = new DefaultLexer();
        $tokens = $lexer->run($content);
        $filename = $kernel->locateResource('@DanPluginDiaryBundle/Test/providers/Lexer/DefaultLexer/tokens.yml');
//        $content = file_put_contents($filename, Yaml::dump($tokens));
        $expected = Yaml::parse(file_get_contents($filename));
        
        $this->assertEquals($expected, $tokens);
    }
    
}