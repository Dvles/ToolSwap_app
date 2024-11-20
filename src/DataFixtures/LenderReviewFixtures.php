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
        $positiveStatements = [
            "Super personne.",
            "Curieuse rencontre.. ",
            "Iel m'a beaucoup aidé. ",
            "ToolSwapper deluxe! ",
            "My neighbout is amazing. ",
            "🧐🧐",
            "🥰",
            "🥹",
            "🫶🫶🫶",
            "Qulle belle rencontre! ",
            "Grazie mille raggaza!!!! ",
            "Who would've guessed this!",
            "OMG!!",
            "CAN'T BELIEBE THIS PERSON",
            "GOOD VIBES!",
            "Okay..",
            "Elle m'a donné plein de tips =)",
            "Well..🥲",
            "SAVE MY LIFE!!",
            "THANKS TOOLSWAP FOR THIS COMMUNITY 😍 ",
            "Ce ToolSwapper est juste INCR 🥹🥹",
            "Mmmm...",
            "Merci ToolSwap! ",
            "Alors..",
            "Donc, ",
            "Comment est-ce possible? ",
            "Merci pour tout ToolSwapper!",
            "Thx ToolSwapper!",
            "Que des belles personnes sur cette appli! ",
            "Très sympathique!",
            "Piouf! Iel m'a offert un café 🥹 ",
            "Il m'a offert du thé!!",
            "Bonne vibes 400%!!",
        ];

        $negativeStatements=[
            "😭😭😭😭 ", "Waste of air guys..", "This person has not been helpful at all!", "So dissapointed", "Où est partie la bienveillance?", "Mais, pourquoi? ", "Très malpoli!", "Intimidant..", "Cette personne est honteuse!", "FAKE PROFILE - ATTENTION",  "🤬", "😔", "😔😔", "🤑🤑.. Escroc!", "Déçue ", "Not very nice..", "Gosh! Ban this person! ", "OSKOUR!! ", "Hooo secours","😭 ", " =( ", "Cancelled the ToolSwap in front of my face! "
        ];





        for ($i = 0; $i < 50; $i++) {
            
            $rating = rand(0, 5);
                
            // Randomizer of statements according to rating
            if ($rating < 3) {
                $openingStatement = $negativeStatements;
            } else {
                $openingStatement = $positiveStatements;
            }
    
            // Calculate randomizing for the presence of comments
            $comment = $faker->randomElement($openingStatement) . " " . $faker->paragraph(1);
            // ensuring that userBeingReviewed =! userLeavingReview 
            // fetch random tool + it's user (ID and object)
            $randomNumber1 = rand(0, count($tools) - 1);
            $randomTool = $tools[$randomNumber1];
            $userBeingReviewed = $randomTool->getOwner();


            // fetch random user ID and object
            $randomNumber2 = rand(0, count($users) - 1);
            $randomUser = $users[$randomNumber2];
            $userLeavingReview = $randomUser->getId();

            // while both users are the same, keep fetching random user ID and object
            while ($userBeingReviewed === $userLeavingReview) {
                $randomNumber2 = rand(0, count($users) - 1);
                $randomUser = $users[$randomNumber2];
                $userLeavingReview = $randomUser->getId();
            }

            $lenderReview = new LenderReview();
            $lenderReview->setRating($rating);
            $lenderReview->setComments($comment);
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
