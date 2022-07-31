<?php

namespace App\BinanceApiClient;

use App\Trait\CollectionTrait;

class KlineCollection
{

    use CollectionTrait;

    /**
     * @param Kline[] $collection
     */
    public function __construct(
        protected array $collection = [],
    ) {
    }

    public static function itemsClassName(): string
    {
        return Kline::class;
    }
    /**
     * @param $rawData
     * @return static
     */
    public static function buildFromApiData($rawData): static
    {
        $klines = [];
        foreach ($rawData as $klineData) {
            $klines[] = new Kline($klineData);
        }
        return new static($klines);
    }

}
