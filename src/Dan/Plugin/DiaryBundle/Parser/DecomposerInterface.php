<?php

namespace Dan\Plugin\DiaryBundle\Parser;

interface DecomposerInterface
{
    public function decompose($content);
}