<?php

namespace Dan\CoreBundle\Controller;

use JMS\Serializer\SerializationContext;
use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Controller extends BaseController
{

    protected function getCurrentRoute()
    {
        $request = $this->getRequest();
        $router = $this->get('router');
        $args = $router->match($request->getPathInfo());
        $route = $args['_route'];
        foreach($args as $key => $value) {
            if ($key[0]=='_') {
                unset($args[$key]);
            }
        }
        return array('route' => $route, 'args' => $args);
    }
    
    protected function getFromRoute()
    {
        $route = $this->get('session')->get('from_route');
        return $route;
    }
    
    protected function setFromRoute($route)
    {
        $this->get('session')->set('from_route', $route);
    }
    
    protected function setCurrentFromRoute()
    {
        $this->setFromRoute($this->getCurrentRoute());
    }
    
    protected function getRepository($name)
    {
        return $this->getDoctrine()->getManager()->getRepository($name);
    }
    
    protected function deserialize($class, Request $request, $format = 'json')
    {
        $serializer = $this->get('serializer');
        $validator = $this->get('validator');

        try {
            $entity = $serializer->deserialize(
                    $request->getContent(), $class, $format
            );
        } catch (\RuntimeException $e) {
            throw new HttpException(400, $e->getMessage());
        }
        if (count($errors = $validator->validate($entity))) {
            return $errors;
        }

        return $entity;
    }
    
    protected function serialize($entity, $format = 'json')
    {
        $serializer = $this->get('serializer');
        $context = new SerializationContext();
        $context->setSerializeNull(false);
        return $serializer->serialize($entity, $format, $context);
    }
    
    protected function addFlash($type, $message) {
        $this->get('session')->getFlashBag()->add( $type, $message );
    }
    
}