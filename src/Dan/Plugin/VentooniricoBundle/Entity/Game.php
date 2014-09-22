<?php

namespace Dan\Plugin\VentooniricoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Yaml\Yaml;
use JMS\Serializer\Annotation as Serializer;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Game
 *
 * @ORM\Table(name="dan_vo_game", indexes={@ORM\Index(name="bgg_id", columns={"bgg_id"})})
 * @ORM\Entity(repositoryClass="Dan\Plugin\VentooniricoBundle\Entity\Repository\GameRepository")
 * @Serializer\ExclusionPolicy("all")
 * @Serializer\AccessType("public_method")
 */
class Game
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Expose
     * @Serializer\Type("integer")
     * //Serializer\ReadOnly
     */
    private $id;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="bgg_id", type="string", length=255)
     * @Serializer\Expose
     * @Serializer\Type("string")
     */
    private $bggId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @Serializer\Expose
     * @Serializer\Type("string")
     */
    private $name;

    /**
     * @var array
     *
     * @ORM\Column(name="owners", type="simple_array")
     * @Serializer\Expose
     * @Serializer\Type("array<string>")
     */
    private $owners;
    
    /**
     * @var array
     * @ORM\OneToMany(targetEntity="Desire", mappedBy="game")
     * @ORM\OrderBy({"id" = "DESC"})
     * @Serializer\Expose
     * @Serializer\Type("ArrayCollection<Dan\Plugin\VentooniricoBundle\Entity\Desire>")
     */
    private $desires;

    /**
     * @var string
     *
     * @ORM\Column(name="thumbnail", type="text")
     * @Serializer\Expose
     * @Serializer\Type("string")
     */
    private $thumbnail;

    /**
     * @var integer
     *
     * @ORM\Column(name="min_players", type="integer")
     * @Serializer\Expose
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("minPlayers")
     */
    private $minPlayers;

    /**
     * @var integer
     *
     * @ORM\Column(name="max_players", type="integer")
     * @Serializer\Expose
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("maxPlayers")
     */
    private $maxPlayers;

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

    public function __construct($item=null, $options = null)
    {
        if (isset($item)) {
            $this->loadFromItem($item, $options);
        }
    }
    
    public function loadFromItem($item, $options=null)
    {
        if (isset($options)) {
            if (isset($options['user'])) {
                $attributes = $item->status[0]->attributes();
                if ( (bool)(int)$attributes['own']) {
                    $this->addOwner($options['user']);
                }
            }
            if (isset($options['owners'])) {
                $this->setOwners($options['owners']);
            }

            if (isset($options['owner'])) {
                $this->addOwner($options['owner']);
            }
        }
        
        $comment = trim((string)$item->comment);
        if ($comment) {
            try {
                $data = Yaml::parse($comment);
                if (is_array($data)) {
                    $owner = null;
                    if (isset($data['Owner'])) {
                        $owner = $data['Owner'];                    
                    }
                    if (isset($data['owner'])) {
                        $owner = $data['owner'];                    
                    }
                    if ($owner) {
                        $this->setOwners(array($owner));
                    }
                }
            } catch (\Exception $e) {}
        }
        $attributes = $item->attributes();
        $this->setBggId((int) $attributes['objectid']);

        $this->setName((string) $item->name);
        $this->setThumbnail((string) $item->thumbnail);
        $attributes = $item->stats[0]->attributes();
        $this->setMinPlayers((int) $attributes['minplayers']);
        $this->setMaxPlayers((int) $attributes['maxplayers']);
    }
    
    
    /**
     * Set id
     *
     * @param integer $id
     * @return Game 
     */
    public function setId($id)
    {
        $this->id = $id;
    
        return $this;
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
     * Set bgg id
     *
     * @param string $bggId
     * @return Game
     */
    public function setBggId($bggId)
    {
        $this->bggId = $bggId;
    
        return $this;
    }
    
    /**
     * Get bgg id
     *
     * @return integer 
     */
    public function getBggId()
    {
        return $this->bggId;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Game
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set owners
     *
     * @param array $owners
     * @return Game
     */
    public function setOwners($owners)
    {
        $this->owners = $owners;
    
        return $this;
    }

    /**
     * Get owners
     *
     * @return array 
     */
    public function getOwners()
    {
        return $this->owners;
    }
    
    public function getOwner()
    {
        return $this->owners[0];
    }
    
    public function addOwner($owner)
    {
        $this->owners[] = $owner;
    }
    
    public function isOwned()
    {
        return (bool)count($this->owners);
    }

    public function setDesires($desires)
    {
        $this->desires = $desires;
    
        return $this;
    }

    public function getDesires()
    {
        return $this->desires;
    }
    
    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("desire")
     */
    public function getLastDesire()
    {
        return $this->desires->first();
    }
    
    public function addDesire($desire)
    {
        $this->desires[] = $desire;
    }

    /**
     * Set thumbnail
     *
     * @param string $thumbnail
     * @return Game
     */
    public function setThumbnail($thumbnail)
    {
        $this->thumbnail = $thumbnail;
    
        return $this;
    }

    /**
     * Get thumbnail
     *
     * @return string 
     */
    public function getThumbnail()
    {
        return $this->thumbnail;
    }

    /**
     * Set minPlayer
     *
     * @param integer $minPlayers
     * @return Game
     */
    public function setMinPlayers($minPlayers)
    {
        $this->minPlayers = $minPlayers;
    
        return $this;
    }

    /**
     * Get minPlayer
     *
     * @return integer 
     */
    public function getMinPlayers()
    {
        return $this->minPlayers;
    }

    /**
     * Set maxPlayers
     *
     * @param integer $maxPlayers
     * @return Game
     */
    public function setMaxPlayers($maxPlayers)
    {
        $this->maxPlayers = $maxPlayers;
    
        return $this;
    }

    /**
     * Get maxPlayers
     *
     * @return integer 
     */
    public function getMaxPlayers()
    {
        return $this->maxPlayers;
    }
    
    
    /**
     * Set createdAt
     *
     * @param datetime $createdAt
     * @return Report
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
     * @return Report
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
    
    
    private function getCompareProperties()
    {
        return array(
            'bggId',
            'owners',
            'thumbnail',
            'minPlayers',
            'maxPlayers',
        );
    }
    
    public function isEquals(Game $game)
    {
        $properties = $this->getCompareProperties();
        foreach($properties as $property) {
            $method = 'get'.ucfirst($property);
            if ($this->$method() != $game->$method()) {
                return false;
            }
        }
        return true;
    }
    
    public function merge(Game $game)
    {
        $properties = $this->getCompareProperties();
        foreach($properties as $property) {
            $setMethod = 'set'.ucfirst($property);
            $getMethod = 'get'.ucfirst($property);
            $this->$setMethod($game->$getMethod());
        }
        return true;
    }
}
