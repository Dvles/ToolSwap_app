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
        $month = random_int(11, 12);
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

    public function getRandomDate2025()
    {
        $month = random_int(1, 6);
        switch ($month) {
            case 2:
                $maxDay = 28;
                break;
            case 4:
            case 6:
                    $maxDay = 30;
                    break;
            default:
                $maxDay = 31;
                break;
        }
        $day = random_int(1, $maxDay);
        $monthFormatted = str_pad($month, 2, '0', STR_PAD_LEFT);
        $dayFormatted = str_pad($day, 2, '0', STR_PAD_LEFT);
        return '2025-' . $monthFormatted . '-' . $dayFormatted;
    }

    public function getRandomPastDate()
    {
        $month = random_int(3, 10);

        switch ($month) {
            case 3:
            case 5:
            case 7:
            case 8:
            case 10:
                $maxDay = 31;
                break;
            case 4:
            case 6:
            case 9:
                $maxDay = 30;
                break;
            default:
                $maxDay = 28;
                break;
        }

        $day = random_int(1, $maxDay);
        $monthFormatted = str_pad($month, 2, '0', STR_PAD_LEFT);
        $dayFormatted = str_pad($day, 2, '0', STR_PAD_LEFT);
        return '2024-' . $monthFormatted . '-' . $dayFormatted;
    }


    public function load(ObjectManager $manager): void
    {
        // retrieve key users created in UserFixtures
        $user1 = $this->getReference('user1');
        $user2 = $this->getReference('user2');

        // Ensure users are found for consistent data handling
        if (!$user1) {
            throw new \Exception("User 1 not found");
        }

        if (!$user2) {
            throw new \Exception("User 2 not found");
        }

        // Instead of fetching by ID, get it by reference
        $toolCategory = $this->getReference('tool_category_3');

        if (!$toolCategory) {
            throw new \Exception("Tool category not found.");
        }

        if (!$toolCategory) {
            throw new \Exception("Tool category not found.");
        }


        // Create user1's tool
        $user1Tool = new Tool();
        $user1Tool->setName('Échelle');
        $user1Tool->setOwner($user1);
        $user1Tool->setToolCategory($toolCategory);
        $user1Tool->setToolCondition('neuf');
        $user1Tool->setDescription("Acheté chez Brico l'été derier - Mon échelle All Round est une échelle en aluminium léger qui convient parfaitement au bricolage dans et aux alentours de la maison. Equipée de sangles de sécurité, stabilisateur et roulettes de façade. Comfort de travail supplémentaire grâce à la large (32 mm) surface d'appui horizontale de l'échelon en D. Le stabilisateur empêche tout enfoncement. Garantie encore valable 5 ans - profitons ensemble!");
        $user1Tool->setPriceDay(mt_rand(0, 5));
        $user1Tool->setImageTool('https://res.cloudinary.com/dzqge7ico/image/upload/v1738328316/ToolSwap_placeholder_cxpuyz.webp');
        $manager->persist($user1Tool);

        // Create user2's tool
        $user2Tool = new Tool();
        $user2Tool->setName('Niveau laser');
        $user2Tool->setOwner($user2);
        $user2Tool->setToolCategory($toolCategory);
        $user2Tool->setToolCondition('vieux');
        $user2Tool->setDescription("Ce laser en croix de Sencys possède un rayon rouge et projette des lignes horizontales et verticales. Il est un peu vieux, mais peut toujours servir! Je suis à la retraite en construction et il est temps qu'il profite à quelqu'un! =)");
        $user2Tool->setPriceDay(0);
        $user2Tool->setImageTool('https://res.cloudinary.com/dzqge7ico/image/upload/v1738328316/ToolSwap_placeholder_cxpuyz.webp');
        $manager->persist($user2Tool);

        $manager->flush();

        // retrieve tools again to ensure they are persisted
        $repTools = $manager->getRepository(Tool::class);
        $tools = $repTools->findAll();


        // Random ToolAvailabilities for each tool
        foreach ($tools as $tool) {
            $usedStartDates = []; // Track used dates for this tool
            for ($i = 0; $i < 20; $i++) {
                do {
                    $date = $this->getRandomDate();
                } while (in_array($date, $usedStartDates)); // Ensure uniqueness for this tool

                $usedStartDates[] = $date;

                $startDate = new \DateTime($date . ' 10:00:00');
                $endDate = clone $startDate;

                $toolAvailability = new ToolAvailability();
                $toolName = $tool->getName();
                $user = $tool->getOwner();

                // Check if the user exists
                if (!$user) {
                    throw new \Exception("Tool {$toolName} has no owner");
                }

                if (!$tool || !$tool->getToolCategory()) {
                    throw new \Exception("Tool {$toolName} is missing a valid category or does not exist.");
                }


                // $toolAvailability->setTitle($toolName);
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

        // Random ToolAvailabilities in 2025 for each tool
        foreach ($tools as $tool) {
            $usedStartDates = []; 
            for ($i = 0; $i < 60; $i++) {
                do {
                    $date = $this->getRandomDate2025();
                } while (in_array($date, $usedStartDates)); 

                $usedStartDates[] = $date;

                $startDate = new \DateTime($date . ' 10:00:00');
                $endDate = clone $startDate;


                $toolAvailability = new ToolAvailability();
                $toolName = $tool->getName();
                $user = $tool->getOwner();

                // Check if the user exists
                if (!$user) {
                    throw new \Exception("Tool {$toolName} has no owner");
                }

                if (!$tool || !$tool->getToolCategory()) {
                    throw new \Exception("Tool {$toolName} is missing a valid category or does not exist.");
                }


                // $toolAvailability->setTitle($toolName);
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

        // Random Past ToolAvailability
        foreach ($tools as $tool) {
            $usedStartDates = []; // Track used dates for this tool
            for ($i = 0; $i < 20; $i++) {
                do {
                    $date = $this->getRandomPastDate();
                } while (in_array($date, $usedStartDates));

                $usedStartDates[] = $date;

                $startDate = new \DateTime($date . ' 10:00:00');
                $endDate = clone $startDate;

                $toolAvailability = new ToolAvailability();
                $toolName = $tool->getName();
                $user = $tool->getOwner();

                // Check if the user exists
                if (!$user) {
                    throw new \Exception("Tool {$toolName} has no owner");
                }

                if (!$tool || !$tool->getToolCategory()) {
                    throw new \Exception("Tool {$toolName} is missing a valid category or does not exist.");
                }


                // Set ToolAvailability attributes
                $toolAvailability->setStart($startDate);
                $toolAvailability->setEnd($endDate);
                $toolAvailability->setBackgroundColor('rgba(255, 179, 71, 1)');
                $toolAvailability->setBorderColor('rgba(255, 140, 0, 1)');
                $toolAvailability->setTextColor('#000000');
                $toolAvailability->setTool($tool);
                $toolAvailability->setUser($user);
                $toolAvailability->setIsAvailable(false); 

                $manager->persist($toolAvailability);
            }
        }


        // User 1 ToolAvailability 
        for ($i = 0; $i < 10; $i++) {
            $usedStartDates = []; 
            do {
                $date = $this->getRandomPastDate();
            } while (in_array($date, $usedStartDates)); 

            $usedStartDates[] = $date;

            $startDate = new \DateTime($date . ' 10:00:00');
            $endDate = clone $startDate;

            $toolAvailabilityUser1 = new ToolAvailability();
            $toolAvailabilityUser1->setStart($startDate);
            $toolAvailabilityUser1->setEnd($endDate);
            $toolAvailabilityUser1->setBackgroundColor('rgba(255, 179, 71, 1)');
            $toolAvailabilityUser1->setBorderColor('rgba(255, 140, 0, 1)');
            $toolAvailabilityUser1->setTextColor('#000000');
            $toolAvailabilityUser1->setTool($user1Tool);
            $toolAvailabilityUser1->setUser($user1);
            $toolAvailability->setIsAvailable(false); 


            $manager->persist($toolAvailabilityUser1);
        }

        $usedStartDates = [];  
        for ($i = 0; $i < 25; $i++) {
            do {
                $date = $this->getRandomDate2025();
            } while (in_array($date, $usedStartDates)); 
        
            $usedStartDates[] = $date; 

            $startDate = new \DateTime($date . ' 10:00:00');
            $endDate = clone $startDate;

            $toolAvailabilityUser1 = new ToolAvailability();
            $toolAvailabilityUser1->setStart($startDate);
            $toolAvailabilityUser1->setEnd($endDate);
            $toolAvailabilityUser1->setBackgroundColor('rgba(255, 179, 71, 1)');
            $toolAvailabilityUser1->setBorderColor('rgba(255, 140, 0, 1)');
            $toolAvailabilityUser1->setTextColor('#000000');
            $toolAvailabilityUser1->setTool($user1Tool);
            $toolAvailabilityUser1->setUser($user1);

            $manager->persist($toolAvailabilityUser1);
        }


        // User 2 ToolAvailability 
        $usedStartDates = [];  
        for ($i = 0; $i < 25; $i++) {
            do {
                $date = $this->getRandomDate2025();
            } while (in_array($date, $usedStartDates));
        
            $usedStartDates[] = $date; 

            $startDate = new \DateTime($date . ' 10:00:00');
            $endDate = clone $startDate;

            $toolAvailabilityUser2 = new ToolAvailability();
            $toolAvailabilityUser2->setStart($startDate);
            $toolAvailabilityUser2->setEnd($endDate);
            $toolAvailabilityUser2->setBackgroundColor('rgba(255, 179, 71, 1)');
            $toolAvailabilityUser2->setBorderColor('rgba(255, 140, 0, 1)');
            $toolAvailabilityUser2->setTextColor('#000000');
            $toolAvailabilityUser2->setTool($user2Tool);
            $toolAvailabilityUser2->setUser($user2);

            $manager->persist($toolAvailabilityUser2);
        }

        for ($i = 0; $i < 15; $i++) {
            $usedStartDates = []; 
            do {
                $date = $this->getRandomPastDate();
            } while (in_array($date, $usedStartDates)); 

            $usedStartDates[] = $date;

            $startDate = new \DateTime($date . ' 10:00:00');
            $endDate = clone $startDate;

            $toolAvailabilityUser2 = new ToolAvailability();
            $toolAvailabilityUser2->setStart($startDate);
            $toolAvailabilityUser2->setEnd($endDate);
            $toolAvailabilityUser2->setBackgroundColor('rgba(255, 179, 71, 1)');
            $toolAvailabilityUser2->setBorderColor('rgba(255, 140, 0, 1)');
            $toolAvailabilityUser2->setTextColor('#000000');
            $toolAvailabilityUser2->setTool($user2Tool);
            $toolAvailability->setIsAvailable(false); 
            $toolAvailabilityUser2->setUser($user2);

            $manager->persist($toolAvailabilityUser2);
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