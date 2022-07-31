<?php

namespace App\Repository;

use App\Entity\Deal;

interface DealRepositoryInterface
{
    /**
     * @param string $name
     * @param int $limit
     * @return Deal[]
     */
    public function findLast(string $name, int $limit = 50): array;

    /**
     * @param Deal $deal
     * @return void
     */
    public function save(Deal $deal): void;
}
