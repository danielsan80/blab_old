<?php

namespace Dan\UserBundle\Tests\Model;

use Dan\CoreBundle\Test\WebTestCase;

class UserManagerTest extends WebTestCase
{

    public function getFixturesToLoad()
    {
        return array(
            'Dan\UserBundle\DataFixtures\ORM\LoadUserData',
            'Dan\UserBundle\DataFixtures\ORM\LoadGroupData',
        );
    }

    public function testGetMetadataWhenMetadataDoNotExist()
    {
        $container = $this->getContainer();
        $userManager = $container->get('model.manager.user');

        $user = $this->getReference('mario');

        $data = $userManager->getMetadata($user,'test');
        $this->assertNull($data);

        $data = $userManager->getMetadata($user,'test','a.path');
        $this->assertNull($data);

        $data = $userManager->getMetadata($user,'test','a.path', 'aDefaultValue');
        $this->assertEquals('aDefaultValue', $data);

        $data = $userManager->getMetadata($user,'test',null , 'aDefaultValue');
        $this->assertEquals('aDefaultValue', $data);

        $data = $userManager->getMetadata($user,'test',null , 'aDefaultValue', array('Default' => 'Default2'));
        $this->assertEquals('aDefault2Value', $data);

        $data = $userManager->getMetadata($user,'test',null , array('A1', 'A2'), array('A' => 'B'));
        $this->assertEquals(array('B1', 'B2'), $data);

    }

    public function testGetMetadataWhenMetadataExists()
    {
        $container = $this->getContainer();
        $userManager = $container->get('model.manager.user');

        $user = $this->getReference('mario');

        $userManager->setMetadata($user, 'test', 'aValue');
        
        $data = $userManager->getMetadata($user, 'test');
        $this->assertEquals('aValue', $data);

        $data = $userManager->getMetadata($user, 'test', null, 'aDefaultValue');
        $this->assertEquals('aValue', $data);

        $userManager->setMetadata($user, 'test', array(
            'A' => array(
                'AA' => 1,
                'AB' => 2,
            ),
            'B' => 3
        ));

        $this->assertEquals(1, $userManager->getMetadata($user, 'test', 'A.AA'));
        $this->assertEquals(2, $userManager->getMetadata($user, 'test', 'A.AB'));
        $this->assertEquals(3, $userManager->getMetadata($user, 'test', 'B'));
        $this->assertEquals(array('AA' => 1, 'AB' => 2,), $userManager->getMetadata($user, 'test', 'A'));


    }
    
}
