<?php

namespace App\DataFixtures;

use App\Entity\Tool;
use App\Entity\ToolCategory;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ToolFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        
        $faker = Factory::create("fr_BE");
        
        // get 'user' repo to assign to new object 
        //->'tool' object can't have 'userOfTool' property null
        $repUsers = $manager->getRepository(User::class);
        $users = $repUsers->findAll();

        // get 'toolCategory' repo to assign to new object 
        //->'tool' object can't have 'toolCategory' property null
        $repToolCategories = $manager->getRepository(ToolCategory::class);
        $toolCategories = $repToolCategories->findAll();

        // Create a mapping of category names to category objects
        $categoryMap = [];
        foreach ($toolCategories as $category) {
            $categoryMap[$category->getName()] = $category;
        }

        // Tools for 'Électroménager' (Home Appliances as per ToolCategory static fixtures)
        $electromenagerTools = [
            'Sèche-cheveux',        // Hairdryer
            'Aspirateur',           // Vacuum cleaner
            'Four à micro-ondes',   // Microwave oven
            'Grille-pain',          // Toaster
            'Mixeur',               // Blender
            'Cafetière',            // Coffee maker
            'Fer à repasser',       // Iron
            'Lave-linge',           // Washing machine
            'Réfrigérateur',        // Refrigerator
            'Lave-vaisselle',       // Dishwasher
            'Ventilateur',          // Fan
            'Friteuse',             // Deep fryer
            'Bouilloire électrique',// Electric kettle
            'Cuisinière électrique',// Electric stove
            'Centrifugeuse',        // Juicer
        ];

        // Tools for 'Jardinage' (Gardening)
        $jardinageTools = [
            'Pelle',                // Shovel
            'Râteau',               // Rake
            'Tondeuse à gazon',      // Lawn mower
            'Taille-haie',          // Hedge trimmer
            'Brouette',             // Wheelbarrow
            'Sécateur',             // Pruning shears
            'Binette',              // Hoe
            'Arrosoir',             // Watering can
            'Débroussailleuse',     // Brushcutter
            'Pioche',               // Pickaxe
            'Serfouette',           // Weeder
            'Motoculteur',          // Rototiller
            'Tronçonneuse',         // Chainsaw
            'Griffe de jardin',     // Garden claw
            'Tamis de jardin',      // Garden sieve
        ];

        // Tools for 'Construction' (Construction)
        $constructionTools = [
            'Marteau',              // Hammer
            'Perceuse',             // Drill
            'Niveau à bulle',       // Spirit level
            'Scie à métaux',        // Hacksaw
            'Pince',                // Pliers
            'Tournevis',            // Screwdriver
            'Mètre ruban',          // Tape measure
            'Clé à molette',        // Adjustable wrench
            'Ponceuse',             // Sander
            'Ciseau à bois',        // Wood chisel
            'Pied de biche',        // Crowbar
            'Truelle',              // Trowel
            'Bétonnière',           // Cement mixer
            'Échafaudage',          // Scaffolding
            'Meuleuse',             // Angle grinder
        ];

        // realistic conditions
        $toolConditions = ["vieux", "neuf", "bon"];
        
        // conditional per category that is linked to right category Entity
        for ($i = 0; $i < 30; $i++){
            $category = $categoryMap['Électroménager'];

            $tool = new Tool();
            $tool->setName($electromenagerTools[mt_rand(0,14)]);
            $tool->setOwner($users[mt_rand(0, count($users)-1)]);
            $tool->setToolCategory($category);
            $tool->setPriceDay(mt_rand(0,10));
            $tool->setDescription($faker->paragraph(1));
            $tool->setToolCondition($toolConditions[mt_rand(0, count($toolConditions)-1)]);
            $tool->setImageTool('https://via.placeholder.com/500x500');

            $manager->persist($tool);
        }

        for ($i = 0; $i < 30; $i++){

            $category = $categoryMap['Jardinage'];

            $tool = new Tool();
            $tool->setName($jardinageTools[mt_rand(0,14)]);
            $tool->setOwner($users[mt_rand(0, count($users)-1)]);
            $tool->setToolCategory($category);
            $tool->setPriceDay(mt_rand(0,10));
            $tool->setDescription($faker->paragraph(1));
            $tool->setToolCondition($toolConditions[mt_rand(0, count($toolConditions)-1)]);
            $tool->setImageTool('https://via.placeholder.com/500x500');

            $manager->persist($tool);
        }

        for ($i = 0; $i < 30; $i++){
            
            $category = $categoryMap['Construction'];


            $tool = new Tool();
            $tool->setName($constructionTools[mt_rand(0,14)]);
            $tool->setOwner($users[mt_rand(0, count($users)-1)]);
            $tool->setToolCategory($category);
            $tool->setDescription($faker->paragraph(1));
            $tool->setPriceDay(mt_rand(0,10));
            $tool->setToolCondition($toolConditions[mt_rand(0, count($toolConditions)-1)]);
            $tool->setImageTool('https://via.placeholder.com/500x500');

            $manager->persist($tool);
        }


        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ToolCategoryFixtures::class, 
            UserFixtures::class,         
        ];
    }
}
