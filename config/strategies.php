<?php

declare(strict_types=1);

use App\Strategy\MACDStrategy;
use App\Worker\Worker;
use Psr\Container\ContainerInterface;

return function (ContainerInterface $ci, Worker $worker): void {

    $worker->addTask(new MACDStrategy(
        $ci,
        'MACD-15m', // Strategy name
        'BTCBUSD', // Symbol for trading BTC with BUSD
        15, // Market data candlestick period
        '15' // Sum in BUSD for trade
    ), 30 * Worker::SECOND); // Task run period

};
