<?php
namespace Dan\Plugin\DiaryBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Dan\Plugin\DiaryBundle\Entity\Report;
use Doctrine\Common\Collections\ArrayCollection;

use Dan\Plugin\DiaryBundle\Regexp\Helper as RegexpHelper;
use Symfony\Component\DependencyInjection\ContainerAware;

class ReportListener extends ContainerAware
{
    
    private $logger;
    private $regexpHelper;

    public function __construct(RegexpHelper $regexpHelper)
    {
        $this->regexpHelper = $regexpHelper;
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
        $regexps = $this->container->get('model.manager.user_metadata')->getMetadata($user, 'diary', 'regexp');

        $data = $this->regexpHelper->decompose($content, $regexps);
        $entity->setProperties($data['properties']);
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $this->prePersist($args);
    }
}
