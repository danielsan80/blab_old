<?php

namespace Dan\Plugin\TryBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Dan\Plugin\TryBundle\DependencyInjection\Compiler\QueueListCompilerPass;

class DanPluginTryBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new QueueListCompilerPass());
    }
}
