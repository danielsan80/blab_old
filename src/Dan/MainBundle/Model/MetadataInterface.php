<?php

namespace Dan\MainBundle\Model;

interface MetadataInterface
{

    public function setContext($context);

    public function getContext();

    public function setContent($content);

    public function getContent();
}
