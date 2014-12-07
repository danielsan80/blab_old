<?php
namespace Dan\Plugin\WwsBundle;

use Dan\PluginBundle\Plugin\AbstractPlugin;

class Plugin extends AbstractPlugin
{

    public function getCode()
    {
        return 'wws';
    }
    
    public function getName()
    {
        return 'WWS';
    }

    public function getBundleName()
    {
        return 'DanPluginWwsBundle';
    }

    public function getRootRoute()
    {
        return 'wws_index';
    }

}