<?php
namespace Dan\MainBundle\DependencyInjection\Compiler;


use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class PluginCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {

        if (!$container->hasDefinition('dan_main.plugin_manager')) {
            return;
        }

        $definition = $container->getDefinition(
            'dan_main.plugin_manager'
        );

        $taggedServices = $container->findTaggedServiceIds(
            'dan.plugin'
        );
        foreach ($taggedServices as $id => $attributes) {
            $definition->addMethodCall(
                'addPlugin',
                array(new Reference($id))
            );
        }
    }
}