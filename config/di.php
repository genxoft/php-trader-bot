<?php

declare(strict_types=1);

use App\Factory\EntityManagerFactory;
use App\Factory\LoggerFactory;
use App\Factory\PredisFactory;
use App\Repository\DealRepositoryInterface;
use App\Repository\Sqlite\DealRepository;
use DI\ContainerBuilder;
use App\BinanceApiClient\BinanceApiInterface;
use App\Factory\BinanceApiFactory;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Predis\ClientInterface as RedisClientInterface;

$config = include __DIR__ . '/../config/config.php';

$dependencies = [
    BinanceApiInterface::class      => DI\factory(BinanceApiFactory::class),
    EntityManagerInterface::class   => DI\factory(EntityManagerFactory::class),
    LoggerInterface::class          => DI\factory(LoggerFactory::class),

    DealRepositoryInterface::class => DI\create(DealRepository::class)
        ->constructor(DI\get(EntityManagerInterface::class)),
];

if (!empty($config['redis.host'])) {
    $dependencies[RedisClientInterface::class] = DI\factory(PredisFactory::class);
}

$containerBuilder = new ContainerBuilder();

$containerBuilder->addDefinitions(array_merge(
    $config,
    $dependencies
));

return $containerBuilder->build();
