<?php

namespace App\Worker;

class TaskRunner
{
    private int $lastRun;

    public function __construct(
        readonly protected TaskInterface $task,
        readonly protected int $interval,
        readonly protected int $startOffset
    ) {
        $this->lastRun = static::currentTime() - $this->interval + $this->startOffset;
    }

    /**
     * @return bool
     */
    public function run(): bool
    {
        if (($this->lastRun + $this->interval) > static::currentTime()) {
            return true;
        }
        $lastRun = $this->lastRun;
        $this->lastRun = static::currentTime();
        return $this->task->run($lastRun);
    }

    private static function currentTime(): int
    {
        return round(microtime(true) * 1000);
    }
}
