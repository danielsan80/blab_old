<?php
namespace Dan\MainBundle\Plugin;

abstract class AbstractPlugin
{

    abstract public function getCode();
    abstract public function getName();
    abstract public function getRootRoute();
    public function getOrder()
    {
        return 0;
    }
}