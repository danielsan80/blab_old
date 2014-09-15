<?php
namespace Dan\Plugin\ReformBundle;

use Dan\PluginBundle\Plugin\AbstractPlugin;

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

    public function getBundleName()
    {
        return 'PluginReformBundle';
    }

    public function getRootRoute()
    {
        return 'reform_index';
    }

}