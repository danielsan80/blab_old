<?php
namespace Dan\Plugin\VentooniricoBundle;

use Dan\PluginBundle\Plugin\AbstractPlugin;

class Plugin extends AbstractPlugin
{

    public function getCode()
    {
        return 'ventoonirico';
    }
    
    public function getName()
    {
        return 'Ventoonirico';
    }

    public function getBundleName()
    {
        return 'DanPluginVentooniricoBundle';
    }

    public function getRootRoute()
    {
        return 'ventoonirico_index';
    }

    public function getUserRole()
    {
        return 'ROLE_USER';
    }

}