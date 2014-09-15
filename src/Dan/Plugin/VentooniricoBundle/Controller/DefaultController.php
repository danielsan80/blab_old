<?php

namespace Dan\Plugin\VentooniricoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\Yaml\Yaml;

class DefaultController extends Controller
{

    /**
     * @Route("/", name="ventoonirico_index")
     * @Template()
     */
    public function indexAction()
    {

        return array();
    }
}
