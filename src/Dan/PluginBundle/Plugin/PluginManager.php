<?php
namespace Dan\PluginBundle\Plugin;

class PluginManager
{
    private $logger;
    private $plugins;

    public function __construct()
    {
        $this->plugins = array();
    }

    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    public function addPlugin(AbstractPlugin $plugin)
    {
        $this->log('added '.$plugin->getCode());
        $this->plugins[$plugin->getOrder()][] = $plugin;
    }

    public function getPlugins()
    {
        $result = array();
        foreach($this->plugins as $order => $plugins) {
            $result = $result + $plugins;
        }
        return $result;
    }

    private function log($message, $param=array())
    {
        if ($this->logger) {
            $this->logger->info('[PLUGIN] '.$message, $param);
        }
    }
}