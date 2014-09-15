<?php

namespace Dan\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Dan\MainBundle\Model\MetadataInterface;

/**
 * Metadata
 *
 * @ORM\Table(name="dan_metadata")
 * @ORM\Entity(repositoryClass="Dan\MainBundle\Entity\Repository\MetadataRepository")
 */
class Metadata implements MetadataInterface
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="context", type="string", length=255)
     */
    private $context;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="json_array", nullable=true)
     */
    private $content;


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
     * Set context
     *
     * @param string $context
     * @return UserMetadata
     */
    public function setContext($context)
    {
        $this->context = $context;

        return $this;
    }

    /**
     * Get context
     *
     * @return string 
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Set content
     *
     * @param array $content
     * @return UserMetadata
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return array 
     */
    public function getContent()
    {
        return $this->content;
    }
}
