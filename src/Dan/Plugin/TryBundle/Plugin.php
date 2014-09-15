<?php
namespace Dan\Plugin\TryBundle;

use Dan\PluginBundle\Plugin\AbstractPlugin;

class Plugin extends AbstractPlugin
{

    public function getCode()
    {
        return 'try';
    }
    
    public function getName()
    {
        return 'Try';
    }

    public function getBundleName()
    {
        return 'DanPluginTryBundle';
    }

    public function getRootRoute()
    {
        return 'try_index';
    }

    public function getUserRole()
    {
        return 'ROLE_SUPER_ADMIN';
    }

}