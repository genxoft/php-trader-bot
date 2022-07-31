<?php

namespace App\Repository\Sqlite;

use App\Entity\Deal;
use App\Exception\RuntimeException;
use App\Repository\DealRepositoryInterface;
use PDO;

class DealRepository extends AbstractRepository implements DealRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function findLast(string $name, int $limit = 50): array
    {
        $qb = $this->em->createQueryBuilder()
            ->select('d')
            ->from(Deal::class, 'd')
            ->where('d.name = :name')
            ->orderBy('d.transactTime', 'DESC')
            ->setMaxResults($limit)
            ->setParameter('name', $name);
        return $qb->getQuery()->getResult();
    }

    /**
     * @inheritDoc
     */
    public function save(Deal $deal): void
    {
        $this->em->persist($deal);
        $this->em->flush();
    }
}
