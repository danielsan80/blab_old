<?php
namespace Dan\UserBundle\Security;
 
use HWI\Bundle\OAuthBundle\Security\Core\User\FOSUBUserProvider as BaseClass;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use FOS\UserBundle\Model\UserManagerInterface;
 
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use Symfony\Component\Security\Core\User\UserInterface;
 
class FOSUBUserProvider extends BaseClass implements UserProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function connect(UserInterface $user, UserResponseInterface $response)
    {
        $userId = $response->getUsername();
        $property = $this->getProperty($response);
 
        $service = $response->getResourceOwner()->getName();
        $getServiceId = 'get'.ucfirst($service).'Id';
        $setServiceId = 'set'.ucfirst($service).'Id';
        $getServiceAccessToken = 'get'.ucfirst($service).'AccessToken';
        $setServiceAccessToken = 'set'.ucfirst($service).'AccessToken';
 
        $user->$setServiceId($userId);
        $user->$setServiceAccessToken($response->getAccessToken());
 
        $this->userManager->updateUser($user);
    }
 
    /**
     * {@inheritdoc}
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $userId = $response->getUsername();
        $email = $response->getEmail();
        
        
        $service = $response->getResourceOwner()->getName();
        $getServiceId = 'get'.ucfirst($service).'Id';
        $setServiceId = 'set'.ucfirst($service).'Id';
        $getServiceAccessToken = 'get'.ucfirst($service).'AccessToken';
        $setServiceAccessToken = 'set'.ucfirst($service).'AccessToken';
        
        $user = $this->userManager->findUserBy(array($this->getProperty($response) => $userId));
        if (null === $user) {
            $user = $this->userManager->findUserBy(array('email' => $email));
        }
        
        if (null === $user) {
            $user = $this->userManager->createUser();
            $username = $response->getUsername();
            if (!($email = $response->getEmail())) {
                $email = $response->getUsername();
            }

            $pos = strpos($username,'@');
            $pos = $pos!==false?$pos:null;
            $username = substr($email,0,$pos);

            $user->setUsername($username);
            $user->setEmail($email);
            $user->setPassword('');
            $user->setEnabled(true);
        }
        
        if(!$user->getImage()) {
            $picture = $response->getProfilePicture();
            if (isset($picture)) {
                $this->userManager->setUserImage($user, $picture);
            }
            
        }

        $this->connect($user, $response);
        
        return $user;
    }
 
}