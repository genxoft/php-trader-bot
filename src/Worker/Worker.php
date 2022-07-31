<?php

namespace App\Worker;

use Psr\Container\ContainerInterface;

class Worker
{
    public const SECOND = 1000;
    public const MINUTE = 60000;
    public const HOUR = 3600000;

    /**
     * @var TaskRunner[]
     */
    protected array $schedule = [];

    protected bool $stop = false;

    public function __construct(
        protected ContainerInterface $ci
    ) {
    }


    /**
     * @param TaskInterface $task
     * @param int $interval in milliseconds
     * @param int|null $startOffset in milliseconds (if null it will be set random between 0 and interval)
     * @return string
     */
    public function addTask(TaskInterface $task, int $interval, ?int $startOffset = null): string
    {
        $id = static::generateRandomTaskId();
        if ($startOffset == null) {
            $startOffset = mt_rand(0, $interval);
        }
        $this->schedule[$id] = new TaskRunner($task, $interval, $startOffset);

        return $id;
    }

    public function run(): void
    {
        while (!$this->stop) {
            foreach ($this->schedule as $id => $task) {
                if (!$task->run()) {
                    unset($this->schedule[$id]);
                }
            }
            usleep(10);
        }
    }

    private static function generateRandomTaskId(): string
    {
        return base_convert(mt_rand(), 10, 32);
    }
}
