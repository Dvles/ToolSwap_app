<?php

namespace App\DataFixtures;

use App\Entity\LenderReview;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LenderReviewFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {

        $repUser = $manager->getRepository(User::class);
        $users = $repUser->findAll();

        

        for ($i=0; $i<50; $i++){
            
            // ensuring that userBeingReviewed =! userLeavingReview 
            $randomNumber1 = rand(0,count($users)-1);
            $randomNumber2 = rand(0,count($users)-1);
            while ($randomNumber1 === $randomNumber2){
                $randomNumber1 = rand(0,count($users)-1);
            }

            $lenderReview = new LenderReview();
            $lenderReview->setRating(rand(0,5));
            $lenderReview->setUserBeingReviewed($users[$randomNumber1]);
            $lenderReview->setUserLeavingReview($users[$randomNumber2]);
            $manager->persist($lenderReview);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class,
        ];
        
    }
}
