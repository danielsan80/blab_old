<?php

namespace Dan\ShareBundle\Model;

use Dan\UserBundle\Entity\User;
use Dan\ShareBundle\Entity\ShareToken;

class ShareData
{
    private $user;
    private $route;
    private $params;
    private $shareToken;

    public function __construct(User $user = null)
    {
        if (!is_null($user)) {
            $this->setUser($user);
        }
    }

    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setRoute($route)
    {
        $this->route = $route;

        return $this;
    }

    public function getRoute()
    {
        return $this->route;
    }

    public function setParams($params)
    {
        if ($this->params && !is_array($params)) {
            throw new \Exception('Params must be an array');            
        }

        $this->params = $params;

        return $this;
    }

    public function getParams()
    {
        if (!$this->params) {
            return array();
        }
        return $this->params;
    }

    public function setShareToken(ShareToken $shareToken)
    {
        $this->shareToken = $shareToken;

        return $this;
    }

    public function getShareToken()
    {
        return $this->shareToken;
    }
    
    public function getToken()
    {
        if ($this->shareToken) {
            return $this->shareToken->getId();            
        }
        return null;
    }

    public function isOwner(User $user = null)
    {
        if (!$user) {
            return false;
        }
        if (!$this->shareToken) {
            return true;
        }
        if ($user->getId() == $this->shareToken->getUser()->getId()) {
            return true;
        }
        return false;
    }
    
}