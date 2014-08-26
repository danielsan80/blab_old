<?php

namespace Dan\CoreBundle\Model;

class GenericData
{
    private $data;

    public function __construct($data = null)
    {
        if (!is_null($data)) {
            $this->setData($data);
        }
    }

    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    public function getData()
    {
        return $this->data;
    }
    
}