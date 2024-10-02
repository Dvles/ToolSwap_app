<?php

namespace App\DataFixtures;

use App\Entity\Tool;
use App\Entity\ToolAvailability;
use App\Entity\ToolCategory;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ToolAvailabilityFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        
        $repTools = $manager->getRepository(Tool::class);
        $tools = $repTools->findAll();
        
        // random ToolAvailabilities
        foreach ($tools as $tool){

            $toolAvailability = new ToolAvailability();

            $toolName = $tool->getName();
            
            $user = $tool->getOwner();
            $startDate = new \DateTime('2024-11-01 10:00:00');
            $endDate = new \DateTime('2024-11-10 18:00:00'); 

            $toolAvailability->setTitle($toolName);
            $toolAvailability->setStart($startDate);
            $toolAvailability->setEnd($endDate);
            $toolAvailability->setBackgroundColor('rgba(255, 179, 71, 1)');
            $toolAvailability->setBorderColor('rgba(255, 140, 0, 1');
            $toolAvailability->setTextColor('#000000');
            $toolAvailability->setTool($tool);
            $toolAvailability->setUser($user);

            $manager->persist($toolAvailability);

        }
        
        // Add User1 
        $repUsers = $manager->getRepository(User::class);
        $user1= $repUsers->find(1);
        
        // create tool for user1
        $repToolCategories = $manager->getRepository(ToolCategory::class);
        $toolCategory = $repToolCategories->find(3);

        $user1Tool = new Tool();
        $user1Tool->setName('échelle');
        $user1Tool->setOwner($user1);
        $user1Tool->setToolCategory($toolCategory);
        $user1Tool->setToolCondition('neuf');
        $user1Tool->setImageTool('https://via.placeholder.com/500x500');
        $manager->persist($user1Tool);
        

        // user one ToolAvailability x 10
        for ($i = 0; $i < 10 ; $i++){
            
            $startDay = 11;
            $startDate = new \DateTime('2024-11-' . ($startDay + $i) . ' 10:00:00');
            $endDate = new \DateTime('2024-11-' . ($startDay + $i) .' 10:00:00');

            $toolAvailabilityUser1 = new ToolAvailability();

            $toolAvailabilityUser1->setTitle('échelle');
            $toolAvailabilityUser1->setStart($startDate);
            $toolAvailabilityUser1->setEnd($endDate);
            $toolAvailabilityUser1->setBackgroundColor('rgba(255, 179, 71, 1)');
            $toolAvailabilityUser1->setBorderColor('rgba(255, 140, 0, 1');
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
            ToolFixtures::class,
            UserFixtures::class,
            ToolCategoryFixtures::class
        ];
    }
}
