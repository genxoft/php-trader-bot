#!/usr/bin/env php
<?php

declare(strict_types=1);

use App\Worker\Worker;

chdir(dirname(__DIR__));

require_once __DIR__ . '/../vendor/autoload.php';

$container = include __DIR__ . '/../config/di.php';

if ($container->has(\Psr\Log\LoggerInterface::class)) {
    \App\Logger\ErrorHandler\ErrorHandler::register($container->get(\Psr\Log\LoggerInterface::class));
    \App\Logger\ErrorHandler\ExceptionHandler::register($container->get(\Psr\Log\LoggerInterface::class));
}

$worker = new Worker($container);

$strategies = include __DIR__ . '/../config/strategies.php';

$strategies($container, $worker);

$worker->run();
