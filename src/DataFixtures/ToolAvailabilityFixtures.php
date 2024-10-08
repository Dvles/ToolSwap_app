<?php

namespace App\DataFixtures;

use App\Entity\Tool;
use App\Entity\ToolAvailability;
use App\Entity\ToolCategory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;


class ToolAvailabilityFixtures extends Fixture implements DependentFixtureInterface
{
    public function getRandomDate()
    {
        $month = random_int(10, 12);
        switch ($month) {
            case 11:
                $maxDay = 30;
                break;
            default:
                $maxDay = 31;
                break;
        }

        $day = random_int(1, $maxDay);
        $monthFormatted = str_pad($month, 2, '0', STR_PAD_LEFT);
        $dayFormatted = str_pad($day, 2, '0', STR_PAD_LEFT);
        return '2024-' . $monthFormatted . '-' . $dayFormatted;
    }

    public function load(ObjectManager $manager): void
    {
        // retrieve the user that was created in UserFixtures
        $user1 = $this->getReference('user1');

        // Ensure user1 is found for consistent data handling
        if (!$user1) {
            throw new \Exception("User 1 not found");
        }

        $repToolCategories = $manager->getRepository(ToolCategory::class);
        $toolCategory = $repToolCategories->find(3);
        
        // Create user1's tool
        $user1Tool = new Tool();
        $user1Tool->setName('échelle');
        $user1Tool->setOwner($user1);
        $user1Tool->setToolCategory($toolCategory);
        $user1Tool->setToolCondition('neuf');
        $user1Tool->setDescription('A short and sweet description about the amazing tool.');
        $user1Tool->setPriceDay(mt_rand(5,10));
        $user1Tool->setImageTool('https://via.placeholder.com/500x500');
        $manager->persist($user1Tool);

        $manager->flush();

        // retrieve tools again to ensure they are persisted
        $repTools = $manager->getRepository(Tool::class);
        $tools = $repTools->findAll();
        
        
        // Random ToolAvailabilities for each tool
        foreach ($tools as $tool) {
            $existingStartDates = []; // Array to track existing start dates
            for ($i = 0; $i < 30; $i++) {
                // Ensure the date is unique
                $date = $this->getRandomDate();
                $startDate = new \DateTime($date . ' 10:00:00');

                // Check for existing start date
                while (in_array($startDate->format('Y-m-d H:i:s'), $existingStartDates)) {
                    $date = $this->getRandomDate(); // Generate a new date
                    $startDate = new \DateTime($date . ' 10:00:00'); // Create a new start date
                }
                $existingStartDates[] = $startDate->format('Y-m-d H:i:s'); 
                $endDate = new \DateTime($date . ' 10:00:00');

                $toolAvailability = new ToolAvailability();
                $toolName = $tool->getName();
                $user = $tool->getOwner();

                // Check if the user exists
                if (!$user) {
                    throw new \Exception("Tool {$toolName} has no owner");
                }

                $toolAvailability->setTitle($toolName);
                $toolAvailability->setStart($startDate);
                $toolAvailability->setEnd($endDate);
                $toolAvailability->setBackgroundColor('rgba(255, 179, 71, 1)');
                $toolAvailability->setBorderColor('rgba(255, 140, 0, 1)');
                $toolAvailability->setTextColor('#000000');
                $toolAvailability->setTool($tool);
                $toolAvailability->setUser($user); 

                $manager->persist($toolAvailability);
            }
        }

        // User 1 ToolAvailability x 10
        for ($i = 0; $i < 10; $i++) {
            $startDay = 11;
            $startDate = new \DateTime('2024-11-' . ($startDay + $i) . ' 10:00:00');
            $endDate = new \DateTime('2024-11-' . ($startDay + $i) . ' 10:00:00');

            $toolAvailabilityUser1 = new ToolAvailability();
            $toolAvailabilityUser1->setTitle('échelle');
            $toolAvailabilityUser1->setStart($startDate);
            $toolAvailabilityUser1->setEnd($endDate);
            $toolAvailabilityUser1->setBackgroundColor('rgba(255, 179, 71, 1)');
            $toolAvailabilityUser1->setBorderColor('rgba(255, 140, 0, 1)');
            $toolAvailabilityUser1->setTextColor('#000000');
            $toolAvailabilityUser1->setTool($user1Tool);
            $toolAvailabilityUser1->setUser($user1); 

            $manager->persist($toolAvailabilityUser1);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class,
            ToolFixtures::class,
            ToolCategoryFixtures::class
        ];
    }
}
