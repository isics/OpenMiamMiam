<?php

namespace Isics\Bundle\OpenMiamMiamBundle\DependencyInjection\Compiler;

use Symfony\Component\Config\Resource\DirectoryResource;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Finder\Finder;

class ValidatorPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $validatorBuilder = $container->getDefinition('validator.builder');
        $validatorFiles = array();

        $finder = new Finder();
        foreach ($finder->files()->in(__DIR__ . '/../../Resources/config/validation') as $file) {
            $validatorFiles[] = $file->getRealPath();
        }

        $validatorBuilder->addMethodCall('addYamlMappings', array($validatorFiles));

        $container->addResource(new DirectoryResource(__DIR__ . '/../../Resources/config/validation'));
    }
}
