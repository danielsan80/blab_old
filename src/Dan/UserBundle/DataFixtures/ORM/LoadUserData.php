<?php

namespace Dan\UserBundle\DataFixtures\ORM;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Dan\UserBundle\Entity\User;

class LoadUserData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    
    private $container;
    
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
        $userManager = $this->container->get('model.manager.user');
        $imageDir = __DIR__.'/files';

        $user = new User();
        $user->setUsername('admin');
        $user->setDisplayName('Admin');
        $user->setEmail('admin@admin.it');
        $user->setPlainPassword('admin');
        $user->setEnabled(true);
        $user->setConfirmationToken(null);
        $user->addGroup($this->getReference('superadmin'));
        
        $userManager->setUserImage($user, $imageDir.'/admin.png');

        $manager->persist($user);
        $this->setReference('admin', $user);
        
        $user = new User();
        $user->setUsername('mario');
        $user->setDisplayName('Mario');
        $user->setEmail('mario@mario.it');
        $user->setPlainPassword('mario');
        $user->setEnabled(true);
        $user->setConfirmationToken(null);
        $userManager->setUserImage($user, $imageDir.'/mario.jpg');

        $manager->persist($user);
        $this->setReference('mario', $user);
 
        $user = new User();
        $user->setUsername('luigi');
        $user->setDisplayName('Luigi');
        $user->setEmail('luigi@luigi.it');
        $user->setPlainPassword('luigi');
        $user->setEnabled(true);
        $user->setConfirmationToken(null);
        $userManager->setUserImage($user, $imageDir.'/luigi.jpg');

        $manager->persist($user);
        $this->setReference('luigi', $user);

        $manager->flush();
    }

    public function getOrder()
    {
        return 20;
    }

}
