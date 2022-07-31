<?php

declare(strict_types=1);

use App\Strategy\MACDStrategy;
use App\Worker\Worker;
use Psr\Container\ContainerInterface;

return function (ContainerInterface $ci, Worker $worker): void {

    $worker->addTask(new MACDStrategy(
        $ci,
        'MACD-15m',
        'BTCBUSD',
        15,
        '15'
    ), 30 * Worker::SECOND);

    $worker->addTask(new MACDStrategy(
        $ci,
        'MACD-30m',
        'BTCBUSD',
        30,
        '15'
    ), 30 * Worker::SECOND);

    $worker->addTask(new MACDStrategy(
        $ci,
        'MACD-5m',
        'BTCBUSD',
        5,
        '15'
    ), 30 * Worker::SECOND);
};
