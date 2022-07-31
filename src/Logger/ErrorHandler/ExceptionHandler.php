<?php

namespace App\Logger\ErrorHandler;

use Closure;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class ExceptionHandler implements ErrorHandlerInterface
{
    private Closure|null $prevHandler = null;

    public function __construct(
        protected LoggerInterface $logger
    ) {
    }

    public static function register(LoggerInterface $logger): static
    {
        $handler = new static($logger);
        $handler->prevHandler = set_exception_handler($handler->handle(...));
        return $handler;
    }

    private function handle(\Throwable $e): never
    {
        $level = LogLevel::ERROR;
        $this->logger->log(
            $level,
            sprintf('Uncaught Exception %s: "%s" at %s line %s', \get_class($e), $e->getMessage(), $e->getFile(), $e->getLine()),
            ['exception' => $e]
        );

        if (null !== $this->prevHandler) {
            ($this->prevHandler)($e);
        }

        exit(255);
    }
}
