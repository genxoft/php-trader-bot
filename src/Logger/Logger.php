<?php

namespace App\Logger;

use App\Logger\Resolver\ResolverInterface;
use DateTime;
use Psr\Log\AbstractLogger;

class Logger extends AbstractLogger
{
    /**
     * @param ResolverInterface[] $resolvers
     */
    public function __construct(
        /**
         * @var ResolverInterface[]
         */
        protected array $resolvers = []
    ) {
    }

    public function withResolver(ResolverInterface $resolver): static
    {
        $this->resolvers[] = $resolver;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function log($level, \Stringable|string $message, array $context = []): void
    {
        $backTrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        array_pop($backTrace);
        array_pop($backTrace);
        $message = new LogMessage($level, $message, $context, new DateTime('now'), $backTrace);
        $this->resolve($message);
    }

    protected function resolve(LogMessage $message): void
    {
        foreach ($this->resolvers as $resolver) {
            $resolver->resolve($message);
        };
    }
}
