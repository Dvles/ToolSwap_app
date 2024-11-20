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

        // Separate ToolAvailability objects by past and future
        $pastToolAvailabilities = [];
        $futureToolAvailabilities = [];

        $currentDate = new \DateTime();

        // Separate tool availabilities into past and future
        foreach ($toolAvailabilities as $toolAvailability) {
            if ($toolAvailability->getEnd() < $currentDate) {
                $pastToolAvailabilities[] = $toolAvailability;
            } elseif ($toolAvailability->getStart() > $currentDate) {
                $futureToolAvailabilities[] = $toolAvailability;
            }
        }

        // Create BorrowTool for past ToolAvailabilities
        foreach ($pastToolAvailabilities as $toolAvailability) {
            // Get the owner of the tool
            $owner = $toolAvailability->getUser();

            // Select a random user that is not the owner
            $user = $owner;
            do {
                $randomUserIndex = array_rand($users); // Get a random index
                $user = $users[$randomUserIndex];
            } while ($user === $owner); // Ensure selected user is not the owner

            // Create BorrowTool with status "COMPLETED"
            $borrowTool = new BorrowTool();
            $borrowTool->setStartDate($toolAvailability->getStart());
            $borrowTool->setEndDate($toolAvailability->getEnd());
            $borrowTool->setToolAvailability($toolAvailability);
            $borrowTool->setToolBeingBorrowed($toolAvailability->getTool());
            $borrowTool->setUserBorrower($user);
            $borrowTool->setStatus(ToolStatusEnum::COMPLETED);

            $manager->persist($borrowTool);
        }

        // Create BorrowTool for future ToolAvailabilities
        $futureBorrowToolsPerUser = [];

        foreach ($futureToolAvailabilities as $toolAvailability) {
            // Get the owner of the tool
            $owner = $toolAvailability->getUser();

            // Select a random user that is not the owner
            $user = $owner;
            do {
                $randomUserIndex = array_rand($users); // Get a random index
                $user = $users[$randomUserIndex];
            } while ($user === $owner); // Ensure selected user is not the owner

            // Track the number of future borrow tools per user
            if (!isset($futureBorrowToolsPerUser[$user->getId()])) {
                $futureBorrowToolsPerUser[$user->getId()] = 0;
            }

            // Ensure no more than 5 borrow tools per user for future dates
            if ($futureBorrowToolsPerUser[$user->getId()] < 5) {
                // Create BorrowTool with status "PENDING"
                $borrowTool = new BorrowTool();
                $borrowTool->setStartDate($toolAvailability->getStart());
                $borrowTool->setEndDate($toolAvailability->getEnd());
                $borrowTool->setToolAvailability($toolAvailability);
                $borrowTool->setToolBeingBorrowed($toolAvailability->getTool());
                $borrowTool->setUserBorrower($user);
                $borrowTool->setStatus(ToolStatusEnum::PENDING);

                $manager->persist($borrowTool);

                // Increment the counter for this user
                $futureBorrowToolsPerUser[$user->getId()]++;
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
