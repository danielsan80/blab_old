<?php

namespace Dan\PluginBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Dan\PluginBundle\DependencyInjection\Compiler\PluginCompilerPass;

class DanPluginBundle extends Bundle
{

    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new PluginCompilerPass());
    }
}
