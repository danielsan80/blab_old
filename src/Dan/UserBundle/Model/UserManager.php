<?php
namespace Dan\UserBundle\Model;

use FOS\UserBundle\Doctrine\UserManager as BaseUserManager;

use Dan\UserBundle\Entity\User;
use Dan\UserBundle\Entity\UserMetadata;
use Symfony\Component\HttpKernel\KernelInterface;

class UserManager extends BaseUserManager
{
    private $kernel;
    private $imagesDir;

    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }
    
    public function setImageDir($dir)
    {
        $this->imagesDir = $dir;
    }
    
    public function getImagesDir()
    {
        return $this->kernel->getRootDir().$this->imagesDir;
    }
    
    public function setUserImage(User $user, $image) {
        $pi = pathinfo($image);
        $filename = 'user_'.md5($user->getEmail()).'.'.$pi['extension'];
        file_put_contents($this->getImagesDir().'/'.$filename, file_get_contents($image));
        $user->setImage($filename);
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