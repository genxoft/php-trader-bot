<?php

declare(strict_types=1);

namespace App\BinanceApiClient;

use DateTime;

class Kline
{
    public int $openTime;
    public DateTime $openDateTime;
    public string $open;
    public string $high;
    public string $low;
    public string $close;
    public string $volume;
    public int $closeTime;
    public DateTime $closeDateTime;
    public string $quote;
    public string $baseAssetVolume;
    public string $quoteAssetVolume;
    public string $ignore;

    public function __construct(array $rawData)
    {
        $this->openTime = (int) $rawData[0];
        $this->openDateTime = (new DateTime())->setTimestamp((int)($this->openTime / 1000));
        $this->open = (string) $rawData[1];
        $this->high = (string) $rawData[2];
        $this->low = (string) $rawData[3];
        $this->close = (string) $rawData[4];
        $this->volume = (string) $rawData[5];
        $this->closeTime = $rawData[6];
        $this->closeDateTime = (new DateTime())->setTimestamp((int)($this->closeTime / 1000));
        $this->quote = (string) $rawData[7];
        $this->baseAssetVolume = (string) $rawData[8];
        $this->quoteAssetVolume = (string) $rawData[9];
        $this->ignore = (string) $rawData[10];
    }

}
