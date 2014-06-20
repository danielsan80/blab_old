<?php
namespace Dan\CoreBundle\Service;

class CurrentRoute {
    
    private $securityContext;
    
    public function __construct($router, $request) {
        $this->router = $router;
        $this->request = $request;
    }
    
    public function get() {
        $request = $this->request;
        $router = $this->router;
        $args = $router->match($request->getPathInfo());
        $route = $args['_route'];
        foreach($args as $key => $value) {
            if ($key[0]=='_') {
                unset($args[$key]);
            }
        }
        return array('name' => $route, 'args' => $args);
    }
}
