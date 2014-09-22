<?php

namespace Dan\Plugin\VentooniricoBundle\Entity\Decorator;

class BaseEntityDecorator
{
    protected $object;

    public function __construct($object)
    {
        $this->object = $object;
    }

    public function __call($method, $args)
    {
        $methods = array(
            $method,
            'get'.ucfirst($method),
            'is'.ucfirst($method),
        );
        
        $_method = $method;
        foreach($methods as $method) {
            if (method_exists($this->object, $method)) {
                return call_user_func_array(array($this->object, $method), $args);
            }
        }
        
        throw new \Exception('The method '. $_method . ' is not supported');

    }

}
