<?php
namespace Dan\PluginBundle\Plugin;

abstract class AbstractPlugin
{

    abstract public function getCode();
    
    abstract public function getName();

    abstract public function getBundleName();

    abstract public function getRootRoute();

    public function getUserRole()
    {
        return null;
    }

    public function getOrder()
    {
        return 0;
    }
}