<?php

namespace Dan\ShareBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Dan\CoreBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;


use Symfony\Component\Yaml\Yaml;

/**
 * @Route("/share")
 */

class DefaultController extends Controller
{
    
    /**
     * @Route("/{share_token}", name="share")
     * @Method("GET")
     */
    public function shareAction(Request $request)
    {
        $sharer = $this->get('sharer');

        $shareToken = $sharer->getShareTokenFromRequest($request);

        if (!$shareToken) {
            throw $this->createNotFoundException('Unable to find the given token.');
        }

        $route = $shareToken->getRoute();
        $params = $shareToken->getParams();
        $params['share_token'] = $shareToken->getId();

        $router = $this->get('router');
        $route = $router->getRouteCollection()->get($route);

        return $this->forward($route->getDefault('_controller'), $params);
    }
}
