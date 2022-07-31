<?php

declare(strict_types=1);

namespace App\Logger\Resolver;

use App\Logger\LogMessage;
use InvalidArgumentException;
use Predis\ClientInterface;

class RedisResolver implements ResolverInterface
{
    /**
     * @var resource
     */
    protected $stream;

    /**
     * @param ClientInterface $redisClient
     * @param string $key
     * @param array $levels
     */
    public function __construct(
        protected ClientInterface $redisClient,
        protected string $key,
        protected array $levels = [],
        protected bool $backTrace = false,
    ) {

    }

    public function resolve(LogMessage $message): void
    {
        if (!empty($this->levels) && !in_array($message->level, $this->levels)) {
            return;
        }

        $this->redisClient->rpush($this->key, [$this->format($message)]);

    }

    protected function format(LogMessage $message): string
    {
        return json_encode([
            'level'     => $message->level,
            'date'      => $message->time->format("c"),
            'message'   => $message->parseMessage(),
            'trace'     => $this->backTrace ? $message->backtrace : null,
        ]);
    }
}
