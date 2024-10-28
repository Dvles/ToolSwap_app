<?php

namespace App\DataFixtures;

use App\Entity\BorrowTool;
use App\Entity\ToolAvailability;
use App\Entity\User;
use App\Enum\ToolStatusEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class BorrowToolFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $repToolAvailability = $manager->getRepository(ToolAvailability::class);
        $toolAvailabilities = $repToolAvailability->findAll();

        $repUsers = $manager->getRepository(User::class);
        $users = $repUsers->findAll();

        // Ensure there are users to borrow tools from
        if (empty($users)) {
            throw new \Exception("No users found for borrowing.");
        }

        // Random borrowTools
        foreach ($toolAvailabilities as $toolAvailability) {
            // Get the owner of the tool
            $owner = $toolAvailability->getUser();

            // Select a random user that is not the owner
            $user = $owner;
            do {
                $randomUserIndex = array_rand($users); // Get a random index
                $user = $users[$randomUserIndex];
            } while ($user === $owner); // Ensure selected user is not the owner

            // Check if the tool availability is not available
            if (!$toolAvailability->isAvailable()) { 
                $borrowTool = new BorrowTool();
                $borrowTool->setStartDate($toolAvailability->getStart());
                $borrowTool->setEndDate($toolAvailability->getEnd());
                $borrowTool->setToolAvailability($toolAvailability); 
                $borrowTool->setToolBeingBorrowed($toolAvailability->getTool());
                $borrowTool->setUserBorrower($user);
                $borrowTool->setStatus(ToolStatusEnum::COMPLETED);

                $manager->persist($borrowTool); 
            }
        }

        $manager->flush(); 
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class,
            ToolFixtures::class,
            ToolAvailabilityFixtures::class
        ];
    }
}
