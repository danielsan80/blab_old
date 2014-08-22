<?php
namespace Dan\Plugin\DiaryBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Dan\Plugin\DiaryBundle\Entity\Report;
use Doctrine\Common\Collections\ArrayCollection;

use Dan\Plugin\DiaryBundle\Regexp\Helper as RegexpHelper;

class ReportListener
{
    
    private $logger;
    private $regexpHelper;
    private $container;

    public function __construct(RegexpHelper $regexpHelper)
    {
        $this->regexpHelper = $regexpHelper;
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

        $regexps = $this->container->get('model.manager.user')->getMetadata(
            $user,
            'diary',
            'regexp',
            $this->regexpHelper->getDefaultRegexp()
        );

        $data = $this->regexpHelper->decompose($content, $regexps);
        $entity->setProperties($data['properties']);
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $this->prePersist($args);
    }
}
