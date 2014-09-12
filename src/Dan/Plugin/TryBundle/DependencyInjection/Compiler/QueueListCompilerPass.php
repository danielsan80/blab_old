<?php
namespace Dan\Plugin\TryBundle\DependencyInjection\Compiler;


use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class QueueListCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {

        if (!$container->hasDefinition('dan_try.queue_list')) {
            return;
        }

        $definition = $container->getDefinition(
            'dan_try.queue_list'
        );

        $taggedServices = $container->findTaggedServiceIds(
            'trt_async.listener.listen'
        );
        foreach ($taggedServices as $id => $attributes) {
            foreach($attributes as $attribute) {
                if (isset($attribute['event'])) {
                    $event = $attribute['event'];
                    break;
                }
            }
            $definition->addMethodCall(
                'addQueue',
                array($event)
            );
        }
    }
}