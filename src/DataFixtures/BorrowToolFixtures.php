<?php

namespace App\DataFixtures;

use App\Entity\BorrowTool;
use App\Entity\ToolAvailability;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class BorrowToolFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        
        // $repToolAvailability = $manager->getRepository(ToolAvailability::class);
        // //$toolAvailability = $rep
        

        // for{$i = 0; $i < 20; $i++}{
        //     $borrowTool = new BorrowTool();
        //     $borrowTool->setRating();
        //     $borrowTool->setComment()


        // };
        
        // // $product = new Product();
        // // $manager->persist($product);

        // $manager->flush();
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
