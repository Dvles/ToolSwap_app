<?php

namespace App\DataFixtures;

use App\Entity\Tool;
use App\Entity\ToolReview;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ToolReviewFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create("fr_BE");

        $repTools = $manager->getRepository(Tool::class);
        $tools = $repTools->findAll();

        $repUser = $manager->getRepository(User::class);
        $users = $repUser->findAll();

        // Realistic statement openers for reviews (array)
        $positiveStatements = [
            'Recommendé!', '🌻🌻🌻', "Full love ici!", "Belle solidarité", 'Satisfaite', 'Top du top! ', 'wow', 'woooow', '!!!', 'Super objet!', 
            'Incroyable', 'Nice =) 🌻', 'Top!','Comme indiqué.', 'Merci infiniment!', 'A relouer !', 'Parfait pour l’entretien', 
            'Nickel pour la maison', 'Magnifique 😍', 'Merci ToolSwap!', 'Thx!', 'Amen!', 'yihah','Saved my life!', 'Such a great initiative', 'ToolSwap is THE best! Thanks',
            'Encore une belle surprise ' . "🎈", 'Merci ToolSwap', 
            'Thanks 🌺 ', "Facile d'utilisation", "Agréable à prendre en main", "Top pour petites mains", "Meilleur que neuf!", "Utilisation facile", "Utilisation méga facile"
        ];

        $midStatements = [
            'Correct..!', 'Mitigé', $faker->emoji() . $faker->emoji(), 'Magnifique.. ' . $faker->emoji(), 
            'Encore une belle surprise ', 'Merci ToolSwap', 'Ca va..', "Mouais 😅", "Comme dans la description","🙄", "😑", "🫥🫥", "🫡","😋", "😅😅", "🤫", " J'aime 🤫🤫 !!",
            'Thanks ' . $faker->emoji()
        ];

        $negativeStatements = [
            'Attention :/', 'Mouais,', 'Déçu,', 'Attention, il est cassé!', 
            'Un peu plus petit que sur la photo', 'Un peu rouillé', "Utilisation très difficile", "Dommage", "À jeter!", "Du grand n'importe quoi", "Nul..", "Mauvais", "Bonjour mais aureveoir", "Tristement", "Pourquoi?", "Alors","Donc", "Svp, jeter le!", "Anarque!!!!", "Du vol!", "Why?", "Mais lol alors!"
        ];

        
        foreach ($tools as $tool) {
            // Generate a random number of reviews for each tool (between 3 and 6)
            $numberOfReviews = rand(3, 13);

            for ($i = 0; $i < $numberOfReviews; $i++) {
                $rating = rand(0, 5);
                
                // Randomizer of statements according to rating
                if ($rating === 3) {
                    $openingStatement = $midStatements;
                } elseif ($rating < 3) {
                    $openingStatement = $negativeStatements;
                } else {
                    $openingStatement = $positiveStatements;
                }

                // Calculate randomizing for the presence of comments
                $comment = $faker->randomElement($openingStatement) . " " . $faker->paragraph(1);

                // Ensure user is not the owner of the tool
                $userOfTool = $tool->getOwner();
                $userOfReview = $users[mt_rand(0, count($users) - 1)];
                
                // Avoid selecting the tool's owner as the reviewer
                while ($userOfReview === $userOfTool) {
                    $userOfReview = $users[mt_rand(0, count($users) - 1)];
                }

                // Create a new ToolReview
                $toolReview = new ToolReview();
                $toolReview->setRating($rating);
                $toolReview->setComment($comment);
                $toolReview->setUserOfReview($userOfReview);
                $toolReview->setToolOfReview($tool);

                // Persist the review
                $manager->persist($toolReview);
            }
        }

        // Flush all reviews to the database
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
