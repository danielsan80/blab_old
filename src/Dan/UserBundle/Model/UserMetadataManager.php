<?php
namespace Dan\UserBundle\Model;

use Dan\UserBundle\Entity\User;
use Dan\UserBundle\Entity\UserMetadata;

use Dan\MainBundle\Model\MetadataHelper;

class UserMetadataManager
{
    private $objectManager;
    private $helper;

    public function __construct($objectManager)
    {
        $this->objectManager = $objectManager;
        $this->helper = new MetadataHelper();
    }

    public function getMetadata(User $user, $context, $path = null, $default = null, $params = array())
    {
        $repo = $this->objectManager->getRepository('DanUserBundle:UserMetadata');

        $userMetadata = $repo->findOneBy(array(
            'user' => $user,
            'context' => $context,
        ));

        if (!$userMetadata) {
            $userMetadata = new UserMetadata();
        }

        return $this->helper->getMetadataContent($userMetadata, $path, $default, $params);
    }

    public function setMetadata(User $user, $context, $path = null, $content)
    {
        $repo = $this->objectManager->getRepository('DanUserBundle:UserMetadata');

        $userMetadata = $repo->findOneBy(array(
            'user' => $user,
            'context' => $context,
        ));

        if (!$userMetadata) {
            $userMetadata = new UserMetadata();
            $userMetadata->setUser($user);
            $userMetadata->setContext($context);
        }

        $this->helper->setMetadataContent($userMetadata, $path, $content);

        $this->objectManager->persist($userMetadata);
        $this->objectManager->flush($userMetadata);

    }

    
}