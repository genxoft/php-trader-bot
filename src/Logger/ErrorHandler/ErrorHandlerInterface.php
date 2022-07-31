<?php

namespace App\Logger\ErrorHandler;

use Psr\Log\LoggerInterface;

interface ErrorHandlerInterface
{
    public static function register(LoggerInterface $logger): static;
}
