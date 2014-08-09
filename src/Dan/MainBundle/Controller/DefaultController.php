<?php

namespace Dan\MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="home")
     * @Template()
     */
    public function indexAction()
    {
        $pluginManager = $this->get('dan_main.plugin_manager');

        return array(
            'plugins' => $pluginManager->getPlugins()
        );
    }
}
