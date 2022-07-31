<?php

namespace App\Factory;

use App\Logger\Logger;
use App\Logger\Resolver\RedisResolver;
use App\Logger\Resolver\StreamResolver;
use Predis\ClientInterface as RedisClientInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class LoggerFactory
{
    /**
     * @param ContainerInterface $ci
     * @return LoggerInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $ci): LoggerInterface
    {
        $loggerResolvers = [
            new StreamResolver(fopen('php://output', 'w'), []),
        ];

        if ($ci->has('logfile.app')) {
            $loggerResolvers[] = new StreamResolver(
                fopen($ci->get('logfile.app'), 'a'),
                [LogLevel::EMERGENCY, LogLevel::ALERT, LogLevel::CRITICAL, LogLevel::ERROR, LogLevel::WARNING, LogLevel::NOTICE],
                true
            );
        }

        if ($ci->has('logfile.info')) {
            $loggerResolvers[] = new StreamResolver(fopen($ci->get('logfile.info'), 'a'), [LogLevel::INFO]);
        }

        if ($ci->has('logredis.key') && $ci->has(RedisClientInterface::class)) {
            $loggerResolvers[] = new RedisResolver($ci->get(RedisClientInterface::class), $ci->get('logredis.key'), [LogLevel::INFO]);
        }

        return new Logger($loggerResolvers);
    }

}
