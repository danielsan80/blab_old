<?php

namespace Dan\Plugin\DiaryBundle\Tests\Parser;

use Dan\CoreBundle\Test\WebTestCase;
use Dan\Plugin\DiaryBundle\Parser\Parser;
use Dan\Plugin\DiaryBundle\Parser\Parser\DefaultParser;
use Symfony\Component\Yaml\Yaml;

class ParserTest extends WebTestCase
{
    public function getFixturesToLoad()
    {
        return array();
    }
    
    public function testDefaultParser()
    {
        $kernel = $this->getContainer()->get('kernel');
        $filename = $kernel->locateResource('@DanPluginDiaryBundle/Test/providers/Parser/DefaultParser/content.txt');
        $content = file_get_contents($filename);
        
        $parser = new DefaultParser();
        $parser->setContent($content);
        $data = $parser->execute();
        
        $properties = $parser->getProperties();
        
        $filename = $kernel->locateResource('@DanPluginDiaryBundle/Test/providers/Parser/DefaultParser/properties.yml');
//        file_put_contents($filename, Yaml::dump($properties, 9));
        
        $expected = Yaml::parse(file_get_contents($filename));
                
        $this->assertEquals($expected, $properties);

    }
}