<?php

declare(strict_types=1);

namespace whatwedo\ImportBundle\Tests\App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use whatwedo\ImportBundle\Tests\App\Entity\Event;

/**
 * @method Event|null   find($id, $lockMode = null, $lockVersion = null)
 * @method Event|null   findOneBy(array $criteria, array $orderBy = null)
 * @method array<Event> findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }
}
