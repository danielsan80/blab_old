<?php
namespace Dan\UserBundle\Entity;

use Sonata\UserBundle\Entity\BaseUser as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="dan_user")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\Column(name="google_id", type="string", length=255, nullable=true)
     */
    protected $googleId;
 
    /**
     * @ORM\Column(name="google_access_token", type="string", length=255, nullable=true)
     */
    protected $googleAccessToken;
 
    /**
     * @ORM\Column(name="facebook_id", type="string", length=255, nullable=true)
     */
    protected $facebookId;
 
    /**
     * @ORM\Column(name="facebook_access_token", type="string", length=255, nullable=true)
     */
    protected $facebookAccessToken;
    
    /**
     * @ORM\Column(name="image", type="text", nullable=true)
     */
    protected $image;
    
    public function __construct()
    {
        parent::__construct();
        $this->desires = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }
    
    public function setGoogleId( $googleId )
    {
        $this->googleId = $googleId;
    }

    public function getGoogleId()
    {
        return $this->googleId;
    }
    
    public function setGoogleAccessToken( $accessToken )
    {
        $this->googleAccessToken = $accessToken;
    }

    public function getGoogleAccessToken()
    {
        return $this->googleAccessToken;
    }
    
    public function setFacebookId( $facebookId )
    {
        $this->facebookId = $facebookId;
    }

    public function getFacebookId()
    {
        return $this->facebookId;
    }
    
    public function setFacebookAccessToken( $accessToken )
    {
        $this->facebookAccessToken = $accessToken;
    }

    public function getFacebookAccessToken()
    {
        return $this->facebookAccessToken;
    }
    
    public function setImage( $url )
    {
        $this->image = $url;
    }

    public function getImage()
    {
        return $this->image;
    }
    
}