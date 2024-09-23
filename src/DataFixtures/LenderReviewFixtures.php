<?php

namespace App\DataFixtures;

use App\Entity\LenderReview;
use App\Entity\Tool;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class LenderReviewFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {

        $repUser = $manager->getRepository(User::class);
        $users = $repUser->findAll();

        $repTool = $manager->getRepository(Tool::class);
        $tools = $repTool->findAll();

        $faker = Factory::create("fr_BE");

        

        for ($i=0; $i<50; $i++){
            
            // ensuring that userBeingReviewed =! userLeavingReview 
            // fetch random tool + it's user (ID and object)
            $randomNumber1 = rand(0,count($tools)-1);
            $randomTool = $tools[$randomNumber1];
            $userBeingReviewed = $randomTool->getOwner(); 
         

            // fetch random user ID and object
            $randomNumber2 = rand(0,count($users)-1);
            $randomUser = $users[$randomNumber2];
            $userLeavingReview = $randomUser->getId();

            // while both users are the same, keep fetching random user ID and object
            while ($userBeingReviewed === $userLeavingReview){
                $randomNumber2 = rand(0,count($users)-1);
                $randomUser = $users[$randomNumber2];
                $userLeavingReview = $randomUser->getId();
            }
     
            $lenderReview = new LenderReview();
            $lenderReview->setRating(rand(0,5));
            $lenderReview->setComments($faker->paragraph(1));
            $lenderReview->setUserBeingReviewed($userBeingReviewed);
            $lenderReview->setUserLeavingReview($randomUser);
            $manager->persist($lenderReview);
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
