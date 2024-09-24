<?php

namespace App\DataFixtures;

use App\DataFixtures\ToolFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\Tool;
use App\Entity\ToolAvailability;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class ToolAvailabilityFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $toolRepo = $manager->getRepository(Tool::class);
        $tools = $toolRepo->findAll(); // Get all Tool objects

        foreach ($tools as $tool) {
            $toolAvailability = new ToolAvailability();

            // Variable available dates array
            $availableDates1 = ['Lundi', 'Mardi'];
            $availableDates2 = ['Jeudi', 'Samedi'];
            $availableDates3 = ['Mardi', 'Mercredi', 'Jeudi', 'Samedi', 'Dimanche'];

            if ($tool->getId() % 3 === 0) {
                $toolAvailability->setAvailableDates($availableDates1);
                $toolAvailability->isRecurring(false);
            } elseif ($tool->getId() % 5 === 0) {
                $toolAvailability->setAvailableDates($availableDates2);
                $toolAvailability->isRecurring(true);
            } else {
                $toolAvailability->setAvailableDates($availableDates3);
                $toolAvailability->isRecurring(true);
            }

            // Set the user as the owner of the tool
            $toolAvailability->setUser($tool->getOwner());

            // Set the tool itself
            $toolAvailability->setTool($tool);
            
            $manager->persist($toolAvailability);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class,
            ToolFixtures::class
        ];
    }
}
