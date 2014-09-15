<?php
namespace Dan\MainBundle\Model;

use Dan\MainBundle\Entity\Metadata;

class MetadataManager
{
    private $objectManager;
    private $helper;

    public function __construct($objectManager)
    {
        $this->objectManager = $objectManager;
        $this->helper = new MetadataHelper();
    }

    public function getMetadata($context, $path = null, $default = null, $params = array())
    {
        $repo = $this->objectManager->getRepository('DanMainBundle:Metadata');

        $metadata = $repo->findOneBy(array(
            'context' => $context,
        ));

        if (!$metadata) {
            $metadata = new Metadata();
        }

        return $this->helper->getMetadataContent($metadata, $path, $default, $params);
    }

    public function setMetadata($context, $path = null, $content)
    {
        $repo = $this->objectManager->getRepository('DanMainBundle:Metadata');

        $metadata = $repo->findOneBy(array(
            'context' => $context,
        ));

        if (!$metadata) {
            $metadata = new Metadata();
            $metadata->setContext($context);
        }

        $this->helper->setMetadataContent($metadata, $path, $content);

        $this->objectManager->persist($metadata);
        $this->objectManager->flush($metadata);

    }
}