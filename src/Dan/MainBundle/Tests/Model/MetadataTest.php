<?php

namespace Dan\MainBundle\Tests\Model;

use Dan\CoreBundle\Test\WebTestCase;

class MetadataManagerTest extends WebTestCase
{

    public function test_getMetadataWhenMetadataDoNotExist()
    {
        $container = $this->getContainer();
        $metadataManager = $container->get('model.manager.metadata');

        $data = $metadataManager->getMetadata('test');
        $this->assertNull($data);

        $data = $metadataManager->getMetadata('test','a.path');
        $this->assertNull($data);

        $data = $metadataManager->getMetadata('test','a.path', 'aDefaultValue');
        $this->assertEquals('aDefaultValue', $data);

        $data = $metadataManager->getMetadata('test',null , 'aDefaultValue');
        $this->assertEquals('aDefaultValue', $data);

        $data = $metadataManager->getMetadata('test',null , 'aDefaultValue', array('Default' => 'Default2'));
        $this->assertEquals('aDefault2Value', $data);

        $data = $metadataManager->getMetadata('test',null , array('A1', 'A2'), array('A' => 'B'));
        $this->assertEquals(array('B1', 'B2'), $data);

    }

    public function test_getMetadataWhenMetadataExists()
    {
        $container = $this->getContainer();
        $metadataManager = $container->get('model.manager.metadata');

        $metadataManager->setMetadata('test', null, 'aValue');
        
        $data = $metadataManager->getMetadata('test');
        $this->assertEquals('aValue', $data);

        $data = $metadataManager->getMetadata( 'test', null, 'aDefaultValue');
        $this->assertEquals('aValue', $data);

        $metadataManager->setMetadata('test', null, array(
            'A' => array(
                'AA' => 1,
                'AB' => 2,
            ),
            'B' => 3
        ));

        $this->assertEquals(1, $metadataManager->getMetadata('test', 'A.AA'));
        $this->assertEquals(2, $metadataManager->getMetadata('test', 'A.AB'));
        $this->assertEquals(3, $metadataManager->getMetadata('test', 'B'));
        $this->assertEquals(array('AA' => 1, 'AB' => 2,), $metadataManager->getMetadata('test', 'A'));


    }
    
}
