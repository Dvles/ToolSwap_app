<?php

namespace App\Repository;

use App\Entity\ToolAvailability;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ToolAvailabilityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ToolAvailability::class);
    }

    /**
     * Deactivates ToolAvailability records that have a start date in the past.
     */
    public function deactivateExpiredAvailabilities()
    {
        $currentDate = new \DateTime(); // Current date and time
        $queryBuilder = $this->createQueryBuilder('ta')
            ->update()
            ->set('ta.isAvailable', ':false')
            ->where('ta.start < :currentDate') // Check if start date is in the past
            ->setParameter('currentDate', $currentDate) // Pass current date parameter
            ->setParameter('false', false); // Set isAvailable to false for past events

        return $queryBuilder->getQuery()->execute(); // Execute the update
    }
}
