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


    public function findByFilters(?bool $isFree, ?int $category, ?string $community): array
    {
        $qb = $this->createQueryBuilder('t');
        
        // Filter by "isFree" (priceDay = 0 for free tools)
        if ($isFree !== null) {
            if ($isFree) {
                $qb->andWhere('t.priceDay = 0');
            } else {
                $qb->andWhere('t.priceDay > 0');
            }
            //dd($qb->getQuery()->getResult()); // Check results after "isFree" filter
        }

        
        // Filter by category if provided
        if ($category !== null) {
            $qb->leftJoin('t.toolCategory', 'c')
               ->andWhere('c.id = :category')
               ->setParameter('category', $category);
               //dd($qb->getQuery()->getResult()); // Check results after "category" filter
        }
        
        // Filter by community if provided
        if ($community !== null) {
            $qb->leftJoin('t.owner', 'u')
               ->andWhere('u.community = :community')
               ->setParameter('community', $community);
               //dd($qb->getQuery()->getResult()); // Check results after "category" filter
        }

    
        return $qb->getQuery()->getResult();
    }
    
}
