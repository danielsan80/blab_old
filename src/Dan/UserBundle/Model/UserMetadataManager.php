<?php
namespace Dan\UserBundle\Model;

use Dan\UserBundle\Entity\User;
use Dan\UserBundle\Entity\UserMetadata;

class UserMetadataRetriever
{
    private $objectManager;

    public function __construct($objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function getMetadata(User $user, $context, $path = null, $default = null, $params = array())
    {
        $repo = $this->objectManager->getRepository('DanUserBundle:UserManager');

        $userMetadata = $repo->findOneBy(array(
            'user' => $user,
            'context' => $context,
        ));

        if (!$userMetadata) {
            $userMetadata = new UserMetadata();
        }

        if (!is_null($default)) {
            if (is_null($userMetadata->getContent()) && is_null($path)) {
                $userMetadata->setContent($default);
            }
        }

        if ($path) {
            $result = $userMetadata->getContent();
            $path = explode('.', $path);
            foreach($path as $i => $part) {
                if (!isset($result[$part])) {
                    $result = null;
                    break;
                }
                $result = $result[$part];
            }

            if (is_null($result)) {
                $result = $default;
            }

            $result = $this->replaceParams($result, $params);

            return $result;
        }

        $result = $userMetadata->getContent();
        $result = $this->replaceParams($result, $params);
        return $result;
    }

    public function setMetadata(User $user, $context, $content)
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
        $userMetadata->setContent($content);
        $this->objectManager->persist($userMetadata);
        $this->objectManager->flush($userMetadata);

    }

    private function replaceParams($data, $params) {
        if (!$params) {
            return $data;
        }
        if (!is_array($data)) {
            return strtr($data, $params);            
        }
        foreach($data as $key => $value) {
            $data[$key] = $this->replaceParams($value, $params);
        }
        return $data;
    }
    
}