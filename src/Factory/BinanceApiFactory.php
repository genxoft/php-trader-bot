<?php

declare(strict_types=1);

namespace App\Factory;

use App\BinanceApiClient\BinanceApi;
use App\Exception\ConfigurationException;
use Psr\Container\ContainerInterface;

class BinanceApiFactory
{
    /**
     * @param ContainerInterface $ci
     * @return BinanceApi
     * @throws ConfigurationException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $ci): BinanceApi
    {
        if ($ci->has('binance_api.base_uri')) {
            $baseUri = $ci->get('binance_api.base_uri');
        } else {
            $baseUri = null;
        }

        if (!$ci->has('binance_api.api_key')) {
            throw new ConfigurationException("Binance API configuration not found Api Key");
        }
        if (!$ci->has('binance_api.api_secret')) {
            throw new ConfigurationException("Binance API configuration not found Api Secret");
        }
        if (!$ci->has('binance_api.test')) {
            throw new ConfigurationException("Binance API configuration not found Test");
        }

        return new BinanceApi(
            $ci->get('binance_api.api_key'),
            $ci->get('binance_api.api_secret'),
            $baseUri,
            $ci->get('binance_api.test')
        );
    }
}
