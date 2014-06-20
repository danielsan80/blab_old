<?php

namespace Dan\UserBundle\DataFixtures\ORM;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Dan\UserBundle\Entity\Group;

class LoadGroupData extends AbstractFixture implements OrderedFixtureInterface
{

    public function load(ObjectManager $manager)
    {
        $group = new Group('SuperAdmin');
        $group->addRole('ROLE_SUPER_ADMIN');
        $manager->persist($group);
        $this->addReference('superadmin', $group);
        
        $group = new Group('Admin');
        $group->addRole('ROLE_ADMIN');
        $manager->persist($group);
        $this->addReference('admin', $group);
        
        $manager->flush();
    }
    
    public function getOrder()
    {
        return 10;
    }
}
