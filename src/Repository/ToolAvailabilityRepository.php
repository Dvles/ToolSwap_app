<?php

namespace App\Repository;

use App\Entity\ToolAvailability;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ToolAvailability>
 */
class ToolAvailabilityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ToolAvailability::class);
    }

    // Deactivates ToolAvailability records that have a start date in the past 
    public function deactivateExpiredAvailabilities(){

        $currentDate = new \DateTime();
        $queryBuilder = $this->createQueryBuilder('ta')
        -> update()
        ->set('ta.isAvailable', 'false')
        ->where('ta.start < :currentDate')
        ->setParameter('currentDate', $currentDate);

        return $queryBuilder->getQuery()->execute();

    }

    //    /**
    //     * @return ToolAvailability[] Returns an array of ToolAvailability objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?ToolAvailability
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
