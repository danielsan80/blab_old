<?php
namespace Dan\Plugin\DiaryBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Dan\Plugin\DiaryBundle\Entity\Report;
use Doctrine\Common\Collections\ArrayCollection;

use Dan\Plugin\DiaryBundle\Regexp\Helper as RegexpHelper;
use Dan\UserBundle\Model\UserMetadataRetriever;

class ReportListener
{
    
    private $logger;
    private $regexpHelper;
    private $userMetadataManager;

    public function __construct(RegexpHelper $regexpHelper, UserMetadataManager $userMetadataManager)
    {
        $this->regexpHelper = $regexpHelper;
        $this->userMetadataManager = $userMetadataManager;
    }
    
    public function setLogger($logger)
    {
        $this->logger = $logger;
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
        $regexps = $userMetadataManager->getMetadata($user, 'diary', 'regexp');

        $data = $this->regexpHelper->decompose($content, $regexps);
        $entity->setProperties($data['properties']);
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $this->prePersist($args);
    }
}
