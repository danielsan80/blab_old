<?php
namespace Dan\Plugin\DiaryBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Dan\Plugin\DiaryBundle\Entity\Report;
use Doctrine\Common\Collections\ArrayCollection;

use Dan\Plugin\DiaryBundle\Regexp\Helper as RegexpHelper;
use Dan\Plugin\DiaryBundle\Model\Manager\ReportManager;

class ReportListener
{
    
    private $logger;
    private $regexpHelper;
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }
    
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    public function setContainer($container)
    {
        $this->container = $container;
    }

    
    private function log($message, $param=array())
    {
        if ($this->logger) {
            $this->logger->info('[REPORT] '.$message, $param);
        }
    }
    
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if (!$entity instanceof Report) {
            return;
        }
        
        $content = $entity->getContent();
        $user = $entity->getUser();

        $data = $this->container->get('dan_diary.model.manager.report')->parseContent($content);
        $entity->setProperties($data['properties']);
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $this->prePersist($args);
    }
}
