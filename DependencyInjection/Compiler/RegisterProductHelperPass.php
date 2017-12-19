<?php

namespace CoreShop\Bundle\CoreBundle\DependencyInjection\Compiler;

use CoreShop\Component\Core\Index\ProductClassHelper;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class RegisterProductHelperPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('coreshop.registry.index.class_helpers')) {
            return;
        }

        $implementationId = 'coreshop.implementations.coreshop.product';
        $definitionId = 'coreshop.index.class_helper.product';

        if ($container->hasParameter($implementationId)) {
            $registry = $container->getDefinition('coreshop.registry.index.class_helpers');

            $implementations = $container->getParameter($implementationId);

            foreach ($implementations as $class) {
                $class = str_replace('Pimcore\\Model\\DataObject\\', '', $class);
                $class = str_replace('\\', '', $class);

                $container->setDefinition($definitionId, new Definition(ProductClassHelper::class));
                $registry->addMethodCall('register', [$class, new Reference($definitionId)]);
            }
        }
    }
}