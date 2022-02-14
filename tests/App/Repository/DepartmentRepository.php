<?php

declare(strict_types=1);

namespace whatwedo\ImportBundle\Tests\App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use whatwedo\ImportBundle\Tests\App\Entity\Department;

/**
 * @method Department|null   find($id, $lockMode = null, $lockVersion = null)
 * @method Department|null   findOneBy(array $criteria, array $orderBy = null)
 * @method array<Department> findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method Department        findOneByName(string $name)
 */
final class DepartmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Department::class);
    }
}
