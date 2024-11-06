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
            if ($isFree) {
                $qb->andWhere('t.priceDay = 0');  // Only free tools
            } else {
                $qb->andWhere('t.priceDay > 0');  // Only non-free tools
            }
        }


        // Filter by category if provided
        if ($category !== null) {
            $qb->join('t.toolCategory', 'c')  // Join the ToolCategory entity
                ->andWhere('c.id = :category')  // Filter by category ID
                ->setParameter('category', $category);
        }

        // Filter by community if provided
        if ($community !== null) {
            $qb->join('t.owner', 'u')  // Join the user (owner) of the tool
                ->andWhere('u.community = :community')  // Filter by the community field on the user
                ->setParameter('community', $community);
        }

        return $qb->getQuery()->getResult();
    }
}
