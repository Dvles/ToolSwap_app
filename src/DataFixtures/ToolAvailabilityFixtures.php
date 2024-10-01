<?php

namespace App\DataFixtures;

use App\Entity\Tool;
use App\Entity\ToolAvailability;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ToolAvailabilityFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        
        $repTools = $manager->getRepository(Tool::class);
        $tools = $repTools->findAll();

    
        foreach ($tools as $tool){

            $toolAvailability = new ToolAvailability();

            $toolName = $tool->getName();
            
            $user = $tool->getOwner();
            $startDate = new \DateTime('2024-11-01 10:00:00');
            $endDate = new \DateTime('2024-11-10 18:00:00'); 

            $toolAvailability->setTitle($toolName);
            $toolAvailability->setStart($startDate);
            $toolAvailability->setEnd($endDate);
            $toolAvailability-> setBackgroundColor('rgba(255, 179, 71, 1)');
            $toolAvailability->setBorderColor('rgba(255, 140, 0, 1');
            $toolAvailability->setTextColor('#000000');
            $toolAvailability->setTool($tool);
            $toolAvailability->setUser($user);

            $manager->persist($toolAvailability);

        }
        
        


        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            ToolFixtures::class,
            UserFixtures::class
        ];
    }
}
