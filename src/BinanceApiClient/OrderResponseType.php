<?php

namespace App\BinanceApiClient;

enum OrderResponseType: string
{
    case ACK = "ACK";
    case RESULT = "RESULT";
    case FULL = "FULL";
}
