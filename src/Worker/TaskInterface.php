<?php

namespace App\Worker;

interface TaskInterface
{
    /**
     * @param int $lastRunTime
     * @return bool (false for stop running task)
     */
    public function run(int $lastRunTime): bool;
}
