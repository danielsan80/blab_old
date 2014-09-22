<?php

namespace Dan\Plugin\VentooniricoBundle\Model;
use Dan\Plugin\VentooniricoBundle\Entity\Desire;
use Dan\UserBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Cache\Cache;

class DesireManager
{
    private $entityName = 'DanPluginVentooniricoBundle:Desire';
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }
    
    private function getRepository()
    {
        return $this->em->getRepository($this->entityName);
    }

    public function getDesires()
    {
        $desires = $this->getRepository()->findAll();
        return $desires;
    }

    public function getDesiresByOwner(User $user)
    {
        $desires = $this->getRepository()->findByOwner($user);
        return $desires;
    }
    
}
