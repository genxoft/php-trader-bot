<?php

namespace App\Logger\ErrorHandler;

use Closure;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class ErrorHandler implements ErrorHandlerInterface
{
    public const FATAL_ERRORS = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR];

    private array $errorLevelMap = [
        E_ERROR             => LogLevel::CRITICAL,
        E_WARNING           => LogLevel::WARNING,
        E_PARSE             => LogLevel::ALERT,
        E_NOTICE            => LogLevel::NOTICE,
        E_CORE_ERROR        => LogLevel::CRITICAL,
        E_CORE_WARNING      => LogLevel::WARNING,
        E_COMPILE_ERROR     => LogLevel::ALERT,
        E_COMPILE_WARNING   => LogLevel::WARNING,
        E_USER_ERROR        => LogLevel::ERROR,
        E_USER_WARNING      => LogLevel::WARNING,
        E_USER_NOTICE       => LogLevel::NOTICE,
        E_STRICT            => LogLevel::NOTICE,
        E_RECOVERABLE_ERROR => LogLevel::ERROR,
        E_DEPRECATED        => LogLevel::NOTICE,
        E_USER_DEPRECATED   => LogLevel::NOTICE,
    ];

    private Closure|bool $prevHandler = true;

    private array $lastFatalError = [];

    public function __construct(
        protected LoggerInterface $logger
    ) {
    }

    public static function register(LoggerInterface $logger): static
    {
        $handler = new static($logger);
        $prev = set_error_handler($handler->handle(...), E_ALL | E_STRICT);
        $handler->prevHandler = $prev !== null ? $prev(...) : true;
        register_shutdown_function($handler->shutdownHandle(...));
        return $handler;
    }

    private function handle(int $code, string $message, string $file = '', int $line = 0): bool
    {
        if (!in_array($code, self::FATAL_ERRORS, true)) {
            $level = $this->errorLevelMap[$code] ?? LogLevel::CRITICAL;
            $this->logger->log($level, self::codeToString($code).': '.$message, ['code' => $code, 'message' => $message, 'file' => $file, 'line' => $line]);
        } else {
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            array_shift($trace);
            $this->lastFatalError = ['type' => $code, 'message' => $message, 'file' => $file, 'line' => $line, 'trace' => $trace];
        }

        if ($this->prevHandler === true) {
            return false;
        }
        if ($this->prevHandler instanceof Closure) {
            return (bool) ($this->prevHandler)($code, $message, $file, $line);
        }
        return false;
    }

    private function shutdownHandle(): void
    {
        if (!empty($this->lastFatalError)) {
            $lastError = $this->lastFatalError;
        } else {
            $lastError = error_get_last();
        }

        if (is_array($lastError) && in_array($lastError['type'], self::FATAL_ERRORS, true)) {
            $trace = $lastError['trace'] ?? null;
            $this->logger->log(
                LogLevel::ALERT,
                'Fatal Error ('.self::codeToString($lastError['type']).'): '.$lastError['message'],
                ['code' => $lastError['type'], 'message' => $lastError['message'], 'file' => $lastError['file'], 'line' => $lastError['line'], 'trace' => $trace]
            );
        }
    }

    private static function codeToString(int $code): string
    {
        return match ($code) {
            E_ERROR => 'E_ERROR',
            E_WARNING => 'E_WARNING',
            E_PARSE => 'E_PARSE',
            E_NOTICE => 'E_NOTICE',
            E_CORE_ERROR => 'E_CORE_ERROR',
            E_CORE_WARNING => 'E_CORE_WARNING',
            E_COMPILE_ERROR => 'E_COMPILE_ERROR',
            E_COMPILE_WARNING => 'E_COMPILE_WARNING',
            E_USER_ERROR => 'E_USER_ERROR',
            E_USER_WARNING => 'E_USER_WARNING',
            E_USER_NOTICE => 'E_USER_NOTICE',
            E_STRICT => 'E_STRICT',
            E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
            E_DEPRECATED => 'E_DEPRECATED',
            E_USER_DEPRECATED => 'E_USER_DEPRECATED',
            default => 'Unknown PHP error',
        };
    }
}
