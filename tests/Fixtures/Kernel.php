<?php

declare(strict_types=1);

namespace whatwedo\ImportBundle\Tests\Fixtures;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use whatwedo\ImportBundle\Manager\ImportDataValidator;
use whatwedo\ImportBundle\Manager\ImportManager;
use whatwedo\ImportBundle\Tests\Fixtures\Definition\EventImportDefinition;
use whatwedo\ImportBundle\Tests\Fixtures\Definition\EventImportSpreadSheetDefinition;
use whatwedo\ImportBundle\Tests\Fixtures\Definition\EventPropertyAccessorImportDefinition;
use whatwedo\ImportBundle\Tests\Fixtures\Repository\DepartmentRepository;
use whatwedo\ImportBundle\Tests\Fixtures\Repository\EventRepository;
use whatwedo\ImportBundle\whatwedoImportBundle;
use Zenstruck\Foundry\ZenstruckFoundryBundle;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function __construct()
    {
        parent::__construct('test', true);
    }

    public function registerBundles(): iterable
    {
        return [
            new DoctrineBundle(),
            new FrameworkBundle(),
            new ZenstruckFoundryBundle(),
            new whatwedoImportBundle(),
        ];
    }

    protected function configureContainer(ContainerBuilder $containerBuilder, LoaderInterface $loader): void
    {
        $registerClasses = [
            ImportManager::class,
            ImportDataValidator::class,
            EventImportDefinition::class,
            EventPropertyAccessorImportDefinition::class,
            EventImportSpreadSheetDefinition::class,
            DepartmentRepository::class,
            EventRepository::class,
            DepartmentRepository::class,
        ];

        foreach ($registerClasses as $registerClass) {
            $containerBuilder->register($registerClass)
                ->setAutoconfigured(true)
                ->setAutowired(true)
                ->setPublic(true);
        }

        $containerBuilder->loadFromExtension('framework', [
            'secret' => 'S3CRET',
            'test' => true,
        ]);

        $containerBuilder->loadFromExtension(
            'doctrine',
            [
                'dbal' => [
                    'url' => '%env(resolve:DATABASE_URL)%',
                ],
                'orm' => [
                    'auto_generate_proxy_classes' => true,
                    'auto_mapping' => true,
                    'mappings' => [
                        'Test' => [
                            'is_bundle' => false,
                            'type' => 'annotation',
                            'dir' => '%kernel.project_dir%/tests/Fixtures/Entity',
                            'prefix' => 'whatwedo\ImportBundle\Tests\Fixtures\Entity',
                            'alias' => 'Test',
                        ],
                    ],
                ],
            ]
        );

        $containerBuilder->loadFromExtension(
            'zenstruck_foundry',
            [
                'auto_refresh_proxies' => true,
            ]
        );
    }
}
