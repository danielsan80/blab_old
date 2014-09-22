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
        if ($response = $this->checkUrl('home')) {
            return $response;
        }
        
        return array();
    }
    
    private function checkUrl($route, $args = array())
    {
//        if ($this->getRequest()->getHost() == 'ventoonirico') {
//            return $this->redirect('http://ventoonirico.local.com' . $this->generateUrl());
//        }
        $uri = explode('#', $this->getRequest()->getRequestUri());
        $uri = explode('?', $uri[0]);
        $uri = $uri[0];        
        if (substr($uri, -1, 0) == '/') {
            return $this->redirect(substr($this->generateUrl($route, $args), 0, -1));
        }
        return null;
    }
    
}
