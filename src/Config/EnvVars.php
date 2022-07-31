<?php

namespace App\Config;

enum EnvVars: string
{
    case TRADE_BOT_MODE = "TRADE_BOT_MODE";
    case BINANCE_API_URL = "BINANCE_API_URL";
    case BINANCE_API_KEY = "BINANCE_API_KEY";
    case BINANCE_API_SECRET = "BINANCE_API_SECRET";
    case REDIS_HOST = "REDIS_HOST";
    case REDIS_PORT = "REDIS_PORT";
    case REDIS_PASSWORD = "REDIS_PASSWORD";

    public function fromEnv(mixed $default = null): mixed
    {
        $value = getenv($this->value);
        if ($value === false) {
            return $default;
        }

        return match (strtolower($value)) {
            'true', '(true)' => true,
            'false', '(false)' => false,
            'empty', '(empty)' => '',
            'null', '(null)' => null,
            default => trim($value),
        };
    }

    public function hasEnv(): bool
    {
        $value = getenv($this->value);
        return $value !== false;
    }
}
