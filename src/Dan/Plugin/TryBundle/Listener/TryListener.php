<?php
namespace Dan\Plugin\TryBundle\Listener;

use Trt\AsyncTasksBundle\Listener\ListenerInterface;
use Trt\AsyncTasksBundle\Event\AsyncEventInterface;

class TryListener implements ListenerInterface
{

    private $container;

    public function setContainer($container)
    {
        $this->container = $container;
    }

    public function work(AsyncEventInterface $event)
    {
        $data = $event->getData();
        var_dump($data);
    }

}
