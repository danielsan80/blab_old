<?php

namespace Dan\PluginBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends Controller
{
    /**
     * @Template()
     */
    public function indexAction()
    {
        $pluginManager = $this->get('dan.plugin_manager');

        return array(
            'plugins' => $pluginManager->getPlugins()
        );
    }
}
