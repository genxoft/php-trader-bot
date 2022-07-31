<?php

declare(strict_types=1);

use App\Config\EnvVars;

if (!defined('TRADE_BOT_MODE')) {
    define('TRADE_BOT_MODE', EnvVars::TRADE_BOT_MODE->fromEnv('DEV'));
}

return [
    'binance_api.test'          => TRADE_BOT_MODE !== 'PROD',
    'binance_api.base_uri'      => EnvVars::BINANCE_API_URL->fromEnv('https://api.binance.com'),
    'binance_api.api_key'       => EnvVars::BINANCE_API_KEY->fromEnv(),
    'binance_api.api_secret'    => EnvVars::BINANCE_API_SECRET->fromEnv(),

    'db.dsn' => 'sqlite:///' . realpath(__DIR__ . '/../data') .'/db.sqlite',
//    'db.user' => '',
//    'db.password' => '',

    'redis.host'    => EnvVars::REDIS_HOST->fromEnv(),
    'redis.port'    => EnvVars::REDIS_PORT->fromEnv(6379),
    'redis.password'=> EnvVars::REDIS_PASSWORD->fromEnv(),

    'logfile.app'     => __DIR__ . '/../data/logs/app.log',
    'logfile.info'    => __DIR__ . '/../data/logs/info.log',
    'logredis.key'    => 'trader-bot-log',
];
