<?php

declare(strict_types=1);

namespace App\Logger\Resolver;

use App\Logger\LogMessage;
use InvalidArgumentException;

class StreamResolver implements ResolverInterface
{
    /**
     * @var resource
     */
    protected $stream;

    /**
     * @param resource $stream
     */
    public function __construct(
        $stream,
        protected array $levels = [],
        protected bool $backTrace = false,
    ) {
        if (!is_resource($stream)) {
            throw new InvalidArgumentException('Stream must be a resource');
        }
        $this->stream = $stream;
    }

    /**
     * @param LogMessage $message
     * @return void
     */
    public function resolve(LogMessage $message): void
    {
        if (!empty($this->levels) && !in_array($message->level, $this->levels)) {
            return;
        }
        $text = "[" . $message->level . "] " . $message->time->format("Y-m-d H:i:s.u") . ": " . $message->parseMessage() . PHP_EOL;
        if ($this->backTrace) {
            $text .= "Stack:" . PHP_EOL;
            $text .= $message->parseTrace() . PHP_EOL;
        }
        fwrite($this->stream, $text);
    }
}
