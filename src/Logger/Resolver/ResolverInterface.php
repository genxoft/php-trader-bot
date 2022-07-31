<?php

namespace App\Logger\Resolver;

use App\Logger\LogMessage;

interface ResolverInterface
{
    public function resolve(LogMessage $message): void;
}
