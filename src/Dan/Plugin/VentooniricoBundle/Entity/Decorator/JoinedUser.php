<?php

namespace Dan\Plugin\VentooniricoBundle\Entity\Decorator;

use Dan\Plugin\VentooniricoBundle\Entity\Join;

class JoinedUser extends BaseEntityDecorator
{
    public function getUserId() {
        return $this->object->getUser()->getId();
    }
    
    public function __call($method, $args)
    {
        try {
            return parent::__call($method, $args);
        } catch (\Exception $e) {}
        
        $methods = array(
            $method,
            'get'.ucfirst($method),
            'is'.ucfirst($method),
        );
        $user = $this->object->getUser();
        
        $_method = $method;
        foreach($methods as $method) {
            if (method_exists($user, $method)) {
                return call_user_func_array(array($user, $method), $args);
            }
        }
        
        throw new \Exception('The method '. $_method . ' is not supported');

    }
}
