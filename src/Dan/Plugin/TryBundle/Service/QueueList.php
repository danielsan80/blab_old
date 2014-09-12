<?php
namespace Dan\Plugin\TryBundle\Service;

class QueueList
{

    private $logger;
    private $queues;

    public function __construct()
    {
        $this->queues = array();
    }

    public function getQueues()
    {
        return $this->queues;
    }

    public function addQueue($queue)
    {
        $this->log('added '.$queue);
        $this->queues[] = $queue;
    }

    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    public function log($message, $param=array())
    {
        if ($this->logger) {
            $this->logger->info('[QUEUE] '.$message, $param);
        }
    }
    
}