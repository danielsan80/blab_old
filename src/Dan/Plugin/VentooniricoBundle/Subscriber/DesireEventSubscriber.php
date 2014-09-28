<?php

namespace Dan\Plugin\VentooniricoBundle\Subscriber;

class DesireEventSubscriber implements \JMS\Serializer\EventDispatcher\EventSubscriberInterface
{

    private $em;
    private $desireManager;
    private $logger;
    
    public function __construct($em, $desireManager)
    {
        $this->em = $em;
        $this->desireManager = $desireManager;
    }

    public function setLogger($logger)
    {
        $this->logger = $logger;
    }
    
    private function log($message, $param=array())
    {
        if ($this->logger) {
            $this->logger->info('[SERIALIZER] '.$message, $param);
        }
    }
    
    public static function getSubscribedEvents()
    {
        return array(
            array('event' => 'serializer.pre_deserialize', 'method' => 'onPreDeserialize'),
            array('event' => 'serializer.post_deserialize', 'method' => 'onPostDeserialize'),
            array('event' => 'serializer.post_serialize', 'method' => 'onPostSerialize'),
        );
    }
    
    public function onPreDeserialize(\JMS\Serializer\EventDispatcher\PreDeserializeEvent $event)
    {
        $this->normalizeDesire($event);
        $this->normalizeJoin($event);
    }
    
    public function onPostDeserialize(\JMS\Serializer\EventDispatcher\ObjectEvent $event)
    {
        $this->mergeDesireRelatedObjects($event);
        $this->mergeJoinRelatedObjects($event);
    }
    
    public function onPostSerialize(\JMS\Serializer\EventDispatcher\ObjectEvent $event)
    {
        $type = $event->getType();
        if ($type['name']=='Dan\UserBundle\Entity\User') {
            $desires = $this->desireManager->getDesiresByOwner($event->getObject());
            $event->getVisitor()->addData('desires_count', count($desires));            
//            $event->getVisitor()->addData('desires', $desires );
        }
    }
    
    
    private function normalizeDesire($event)
    {
        $type = $event->getType();
        if ($type['name']=='Dan\Plugin\VentooniricoBundle\Entity\Desire') {
            $data = $event->getData();
            $data = $this->normalizeData($data, 'game');
            $data = $this->normalizeData($data, 'owner');
            $event->setData($data);
        }
    }
    
    private function normalizeJoin($event)
    {
        $type = $event->getType();
        if ($type['name']=='Dan\Plugin\VentooniricoBundle\Entity\Join') {
            $data = $event->getData();
            $data = $this->normalizeData($data, 'desire');
            $data = $this->normalizeData($data, 'user');
            $event->setData($data);
        }
    }
    
    private function normalizeData($data, $key)
    {
        if (isset($data[$key]) && !is_array($data[$key])) {
            $data[$key] = array('id' => $data[$key]);
        }
        return $data;
    }
    
    private function mergeDesireRelatedObjects($event)
    {
        $type = $event->getType();
        
        if ($type['name']=='Dan\Plugin\VentooniricoBundle\Entity\Desire') {
            $this->mergeRelatedObject($event, 'owner');
            $this->mergeRelatedObject($event, 'game');
        }
    }
    private function mergeJoinRelatedObjects($event)
    {
        $type = $event->getType();
        if ($type['name']=='Dan\Plugin\VentooniricoBundle\Entity\Join') {
            $this->mergeRelatedObject($event, 'desire');
            $this->mergeRelatedObject($event, 'user');
        }
    }
    
    private function mergeRelatedObject($event, $relatedObjectName) {
        
        $object = $event->getObject();
        $getRelatedObject = 'get'. ucfirst($relatedObjectName);
        $setRelatedObject = 'set'. ucfirst($relatedObjectName);
        
        $relatedObject = $object->$getRelatedObject();
        if ($relatedObject) {
            $relatedObject = $this->em->merge($relatedObject);
            $this->em->refresh($relatedObject);
            $object->$setRelatedObject($relatedObject);
        }
        
    }

}