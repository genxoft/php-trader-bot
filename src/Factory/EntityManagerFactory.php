<?php

declare(strict_types=1);

namespace App\Factory;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Psr\Container\ContainerInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class EntityManagerFactory
{
    /**
     * @param \Psr\Container\ContainerInterface $ci
     * @return \Doctrine\ORM\EntityManagerInterface
     * @throws \Doctrine\ORM\Exception\ORMException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $ci): EntityManagerInterface
    {
        $dbParams = [
            'url' => $ci->get('db.dsn'),
        ];

        $config = ORMSetup::createAnnotationMetadataConfiguration(
            [
                __DIR__ . '/../Entity'
            ],
            false,
            null,
            new ArrayAdapter()
        );

        return EntityManager::create($dbParams, $config);
    }
}
