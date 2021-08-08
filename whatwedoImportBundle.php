<?php

namespace whatwedo\ImportBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use whatwedo\ImportBundle\DependencyInjection\Compiler\DefinitionPass;

class
whatwedoImportBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new DefinitionPass());
    }
}
