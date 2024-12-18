<?php

namespace App\Repository;

use App\Entity\Tool;
use App\Entity\User;
use App\Entity\BorrowTool;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

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

    public function countCompletedBorrowToolsByLender(User $lender, \DateTime $currentDate): int
    {
        return $this->createQueryBuilder('bt')
            ->select('COUNT(DISTINCT bt.id)')
            ->join('bt.toolBeingBorrowed', 'tool') // Assuming this is the relation between BorrowTool and Tool
            ->where('tool.owner = :lender')
            ->andWhere('bt.endDate < :currentDate') // Completed borrow tools have ended
            ->setParameter('lender', $lender)
            ->setParameter('currentDate', $currentDate)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countPendingBorrowToolsByLender(User $lender, \DateTime $currentDate): int
    {
        return $this->createQueryBuilder('bt')
            ->select('COUNT(DISTINCT bt.id)')
            ->join('bt.toolBeingBorrowed', 'tool') // Assuming this is the relation between BorrowTool and Tool
            ->where('tool.owner = :lender')
            ->andWhere('bt.endDate > :currentDate') // Pending borrow tools haven't ended yet
            ->setParameter('lender', $lender)
            ->setParameter('currentDate', $currentDate)
            ->getQuery()
            ->getSingleScalarResult();
    }

    // use this method to display popuplar tools (with +25 borrowTools)
    public function countBorrowedToolsForTool(Tool $tool): int
    {
        return $this->createQueryBuilder('borrowTool')
            ->select('COUNT(DISTINCT borrowTool.id)')  
            ->where('borrowTool.toolBeingBorrowed = :tool')  
            ->setParameter('tool', $tool)  
            ->getQuery()
            ->getSingleScalarResult();  
    }
}
