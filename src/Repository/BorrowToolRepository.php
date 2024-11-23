<?php

namespace App\Repository;

use App\Entity\BorrowTool;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BorrowTool>
 */
class BorrowToolRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BorrowTool::class);
    }


    public function countBorrowedToolsByBorrower(User $user): int
    {
        return $this->createQueryBuilder('borrowTool')
            ->select('COUNT(DISTINCT borrowTool.id)')  // Use DISTINCT to count unique borrowTool entries
            ->where('borrowTool.userBorrower = :borrower')  // Filter by the borrower
            ->setParameter('borrower', $user)  // Bind the User entity parameter to the query
            ->getQuery()
            ->getSingleScalarResult();  // 
    }

    public function countBorrowedToolsByOwner(User $owner): int
    {
        return $this->createQueryBuilder('borrowTool')
            ->select('COUNT(DISTINCT borrowTool.id)')
            ->join('borrowTool.toolBeingBorrowed', 'tool')
            ->where('tool.owner = :owner')
            ->setParameter('owner', $owner)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countCompletedBorrowToolsByBorrower(User $user, \DateTime $currentDate)
    {
        return $this->createQueryBuilder('bt')
            ->select('COUNT(bt)')
            ->where('bt.userBorrower = :user')
            ->andWhere('bt.endDate < :currentDate')
            ->setParameter('user', $user)
            ->setParameter('currentDate', $currentDate)
            ->getQuery()
            ->getSingleScalarResult();
    }
    
    public function countPendingBorrowToolsByBorrower(User $user, \DateTime $currentDate)
    {
        return $this->createQueryBuilder('bt')
            ->select('COUNT(bt)')
            ->where('bt.userBorrower = :user')
            ->andWhere('bt.endDate > :currentDate')
            ->setParameter('user', $user)
            ->setParameter('currentDate', $currentDate)
            ->getQuery()
            ->getSingleScalarResult();
    }
    
}
