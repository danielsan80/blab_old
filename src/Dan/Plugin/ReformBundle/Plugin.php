<?php
namespace Dan\Plugin\ReformBundle;

use Dan\MainBundle\Plugin\AbstractPlugin;

class Plugin extends AbstractPlugin
{

    public function getCode()
    {
        return 'reform';
    }
    
    public function getName()
    {
        return 'ReForm';
    }

    public function getRootRoute()
    {
        return 'reform_index';
    }

}