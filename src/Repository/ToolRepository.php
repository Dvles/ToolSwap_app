<?php

namespace App\Repository;

use App\Entity\Tool;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tool>
 */
class ToolRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tool::class);
    }


    public function countBorrowedToolsByOwner(User $owner): int
    {
        // Initialize a QueryBuilder instance with alias 't' for the Tool entity
        return $this->createQueryBuilder('t')
            // Select the count of BorrowTool records associated with tools owned by the given user
            ->select('COUNT(b.id)')
            
            // Join the borrowTools relation to link tools with their borrowing records
            ->join('t.borrowTools', 'b')
            
            // Filter by the specified owner, retrieving only the tools owned by this user
            ->where('t.owner = :owner')
            
            // Set the owner parameter with the provided User instance
            ->setParameter('owner', $owner)
            
            // Execute the query and return the count as a single scalar integer result
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countToolsOwnedByOwner(User $owner): int
    {
        // Initialize a QueryBuilder instance with alias 't' for the Tool entity
        return $this->createQueryBuilder('t')
            // Select the count of Tool records
            ->select('COUNT(t.id)')
            
            // Filter by the specified owner to only include tools owned by this user
            ->where('t.owner = :owner')
            
            // Set the owner parameter with the provided User instance
            ->setParameter('owner', $owner)
            
            // Execute the query and return the count as a single scalar integer result
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countFreeToolsOwnedByOwner(User $owner): int
    {
        // Initialize a QueryBuilder instance with alias 't' for the Tool entity
        return $this->createQueryBuilder('t')
            // Select the count of Tool records
            ->select('COUNT(t.id)')
            
            // Filter by the specified owner to only include tools owned by this user
            ->where('t.owner = :owner')
            
            // Additional filter for tools that are available (free to borrow)
            ->andWhere('t.availability = true')
            
            // Set the owner parameter with the provided User instance
            ->setParameter('owner', $owner)
            
            // Execute the query and return the count as a single scalar integer result
            ->getQuery()
            ->getSingleScalarResult();
    }


}
