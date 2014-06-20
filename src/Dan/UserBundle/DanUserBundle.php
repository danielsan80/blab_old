<?php

namespace Dan\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class DanUserBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'SonataUserBundle';
    }
}
