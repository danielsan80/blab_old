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
    private $userMetadataManager;

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
        $content = file_get_contents($image);
        $filename = 'user_'.md5($user->getEmail()).'.jpg';
        file_put_contents($this->getImagesDir().'/'.$filename, $content );
        $user->setImage($filename);
    }

    public function setUserMetadataManager(UserMetadataManager $userMetadataManager)
    {
        $this->userMetadataManager = $userMetadataManager;
    }


    public function getMetadata(User $user, $context, $path = null, $default = null, $params = array())
    {
        return $this->userMetadataManager->getMetadata($user, $context, $path, $default, $params);
    }

    public function setMetadata(User $user, $context, $path, $content)
    {
        return $this->userMetadataManager->setMetadata($user, $context, $path, $content);
    }

}