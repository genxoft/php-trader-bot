<?php

namespace App\BinanceApiClient;

enum TimeInForce: string
{
    case GTC = "GTC";
    case IOC = "IOC";
    case FOK = "FOK";
}
