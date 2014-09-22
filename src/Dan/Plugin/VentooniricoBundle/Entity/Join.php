<?php

namespace Dan\Plugin\VentooniricoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use JMS\Serializer\Annotation as Serializer;
use Dan\Plugin\VentooniricoBundle\Entity\Desire;
use Dan\UserBundle\Entity\User;

/**
 * Join
 *
 * @ORM\Table(name="dan_vo_desire_user")
 * @ORM\Entity(repositoryClass="Dan\Plugin\VentooniricoBundle\Entity\Repository\JoinRepository")
 * @Serializer\ExclusionPolicy("all")
 */
class Join
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Expose
     * @Serializer\Type("integer")
     */
    private $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="Dan\Plugin\VentooniricoBundle\Entity\Desire", inversedBy="joins")
     * @ORM\JoinColumn(name="desire_id", referencedColumnName="id")
     * @Serializer\Expose
     * @Serializer\Type("Dan\Plugin\VentooniricoBundle\Entity\Desire")
     */
    private $desire;
    
    /**
     * @ORM\ManyToOne(targetEntity="Dan\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * @Serializer\Expose
     * @Serializer\Type("Dan\UserBundle\Entity\User")
     */
    private $user;

    /**
     * @var string
     *
     * @ORM\Column(name="note", type="text", nullable=true)
     */
    private $note;

    
    public function __construct(Desire $desire, User $user)
    {
        $this->setDesire($desire);
        $this->setUser($user);
    }

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
     * Set desire
     *
     * @param Desire $desire
     * @return Join
     */
    public function setDesire(Desire $desire)
    {
        $this->desire = $desire;
    
        return $this;
    }

    /**
     * Get desire
     *
     * @return Desire
     */
    public function getDesire()
    {
        return $this->desire;
    }
    
    /**
     * Set user
     *
     * @param User $user
     * @return Join
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
        return $this->user;
    }
    
    /**
     * Set note
     *
     * @param string $note
     * @return Join
     */
    public function setNote($note)
    {
        $this->note = $note;
    
        return $this;
    }

    /**
     * Get note
     *
     * @return string 
     */
    public function getNote()
    {
        return $this->note;
    }
    
    public function setOptions($options)
    {
        if (isset($options['note'])) {
            $this->setNote($options['note']);
        }
    }
}
