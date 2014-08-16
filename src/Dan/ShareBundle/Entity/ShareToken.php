<?php

namespace Dan\ShareBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

use Dan\UserBundle\Entity\User;

/**
 * ShareToken
 *
 * @ORM\Table(name="dan_share_token")
 * @ORM\Entity(repositoryClass="Dan\ShareBundle\Entity\Repository\ShareTokenRepository")
 */
class ShareToken
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="guid")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="\Dan\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

    /**
     * @var string
     *
     * @ORM\Column(name="route", type="string", length=255)
     */
    private $route;

    /**
     * @var array
     *
     * @ORM\Column(name="params", type="json_array")
     */
    private $params;

    /**
     * @var datetime $cratedAt
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    protected $createdAt;

    /**
     * @var datetime $updatedAt
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    protected $updatedAt;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

     /**
     * Set user
     *
     * @param User $user
     * @return Report
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    
        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser()
    {
        if (!$this->user) {
            $this->user = new User();
        }
        return $this->user;
    }

    /**
     * Set route
     *
     * @param string $route
     * @return ShareToken
     */
    public function setRoute($route)
    {
        $this->route = $route;

        return $this;
    }

    /**
     * Get route
     *
     * @return string 
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Set params
     *
     * @param array $params
     * @return ShareToken
     */
    public function setParams($params)
    {
        $this->params = $params;

        return $this;
    }

    /**
     * Get params
     *
     * @return array 
     */
    public function getParams()
    {
        return $this->params;
    }

        /**
     * Set createdAt
     *
     * @param datetime $createdAt
     * @return ShareToken
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
        
        return $this;
    }

    /**
     * Get createdAt
     *
     * @return datetime 
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param datetime $updatedAt
     * @return ShareToken
     */
    public function setUpdatedAt($updatedAt=null)
    {
        
        if (!$updatedAt) {
            $updatedAt = new \DateTime();
        } 
        $this->updatedAt = $updatedAt;
        
        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return datetime 
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }
}
