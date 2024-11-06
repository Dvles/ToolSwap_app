<?php

namespace App\Repository;

use App\Entity\BorrowTool;
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
        return $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->where('t.owner = :owner')
            ->andWhere('t.availability = true')
            ->setParameter('owner', $owner)
            ->getQuery()
            ->getSingleScalarResult();
    }


    public function findByFilters(?bool $isFree, ?int $category, ?int $community): array
    {
        $qb = $this->createQueryBuilder('t');
        
        // Filter by "isFree" (only tools with priceDay = 0 or non-zero)
        if ($isFree !== null) {
            $qb->andWhere('t.priceDay = :priceDay')
               ->setParameter('priceDay', $isFree ? 0 : 1);
        }
        
        // Filter by category if provided
        if ($category !== null) {
            $qb->leftJoin('t.toolCategory', 'c')  // Use LEFT JOIN to avoid filtering out tools with no category
               ->andWhere('c.id = :category')
               ->setParameter('category', $category);
        }
        
        // Filter by community if provided
        if ($community !== null) {
            $qb->leftJoin('t.owner', 'u')  // Use LEFT JOIN for owners, just in case there are tools without owners (or to avoid exclusion)
               ->andWhere('u.community = :community')
               ->setParameter('community', $community);
        }
    
        return $qb->getQuery()->getResult();
    }
    
}
