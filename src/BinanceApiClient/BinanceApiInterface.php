<?php

declare(strict_types=1);

namespace App\BinanceApiClient;

interface BinanceApiInterface
{
    public const BASE_URI = 'https://api.binance.com';

    public function ping(): array;

    public function time(): array;

    /**
     * Post order
     * @param string $symbol
     * @param Side $side
     * @param OrderType $type
     * @param TimeInForce|null $timeInForce
     * @param string|null $quantity
     * @param string|null $quoteOrderQty
     * @param string|null $price
     * @param string|null $newClientOrderId
     * @param string|null $stopPrice
     * @param int|null $trailingDelta
     * @param string|null $icebergQty
     * @param OrderResponseType|null $newOrderRespType
     * @return array
     */
    public function postOrder(
        string $symbol,
        Side $side,
        OrderType $type,
        ?TimeInForce $timeInForce = null,
        ?string $quantity = null,
        ?string $quoteOrderQty = null,
        ?string $price = null,
        ?string $newClientOrderId = null,
        ?string $stopPrice = null,
        ?int $trailingDelta = null,
        ?string $icebergQty = null,
        ?OrderResponseType $newOrderRespType = OrderResponseType::RESULT
    ): array;

    /**
     * Get klines
     * @param string $symbol
     * @param string $interval
     * @param int|null $startTime
     * @param int|null $endTime
     * @param int $limit
     * @return KlineCollection
     */
    public function getKlines(string $symbol, string $interval, ?int $startTime = null, ?int $endTime = null, int $limit = 500): KlineCollection;
}
