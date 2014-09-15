<?php
namespace Dan\PluginBundle\DependencyInjection\Compiler;


use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class PluginCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {

        if (!$container->hasDefinition('dan.plugin_manager')) {
            return;
        }

        $definition = $container->getDefinition(
            'dan.plugin_manager'
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