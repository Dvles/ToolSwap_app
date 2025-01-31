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

        $electromenagerCategory = $repToolCategories->findOneBy(['name' => 'Électroménager']);
        $jardinageCategory = $repToolCategories->findOneBy(['name' => 'Jardinage']);
        $constructionCategory = $repToolCategories->findOneBy(['name' => 'Construction']);

        // Check if all required tool categories exist; if not, throw an exception.
        if (!$electromenagerCategory || !$jardinageCategory || !$constructionCategory) {
            throw new \Exception("One or more categories not found.");
        }

        // Tools for 'Électroménager' (Home Appliances as per ToolCategory static fixtures)
        $electromenagerTools = [
            'Sèche-cheveux',           // Hairdryer
            'Aspirateur',              // Vacuum cleaner
            'Four à micro-ondes',      // Microwave oven
            'Grille-pain',             // Toaster
            'Mixeur',                  // Blender
            'Cafetière',               // Coffee maker
            'Fer à repasser',          // Iron
            'Furêt',                   // Washing machine
            'Réfrigérateur',           // Refrigerator
            'Ventilateur',             // Fan
            'Friteuse',                // Deep fryer
            'Bouilloire électrique',   // Electric kettle
            'Cuisinière électrique',   // Electric stove
            'Centrifugeuse',           // Juicer
            'Grille-pain à croissants', // Croissant toaster
            'Presse-agrumes',          // Citrus press
            'Rôtisserie',              // Rotisserie
            'Hachoir',                 // Meat grinder
            'Appareil à raclette',     // Raclette machine
            'Appareil à fondue',       // Fondue machine
            'Moulin à café',           // Coffee grinder
            'Déshumidificateur',       // Dehumidifier
            'Nettoyeur vapeur',        // Steam cleaner
            'Extracteur de jus',       // Juice extractor
            'Fondue',                  // Fondue machine
            'Pasta maker',             // Pasta machine
            'Four à Tarte Flambée',    // Portable Flammenkuche oven


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
            'Marteau',               // Hammer
            'Perceuse',              // Drill
            'Niveau à bulle',        // Spirit level
            'Scie à métaux',         // Hacksaw
            'Pince',                 // Pliers
            'Tournevis',             // Screwdriver
            'Mètre ruban',           // Tape measure
            'Clé à molette',         // Adjustable wrench
            'Ponceuse',              // Sander
            'Ciseau à bois',         // Wood chisel
            'Pied de biche',         // Crowbar
            'Truelle',               // Trowel
            'Bétonnière',            // Cement mixer
            'Échafaudage',           // Scaffolding
            'Meuleuse',              // Angle grinder
            'Scie circulaire',       // Circular saw
            'Perforateur',           // Hammer drill
            'Clé à pipe',            // Socket wrench
            'Compresseur',           // Air compressor
            'Chiffon abrasif',       // Sandpaper
            'Clé à griffe',          // Pipe wrench
            'Pelle',                 // Shovel
            'Niveau laser',          // Laser level
            'Boulonneuse',           // Impact wrench
            'Cisaille',              // Shears
            'Détecteur de métaux',   // Metal detector
            'Tarière',               // Auger
            'Mèche',                 // Drill bit
            'Boite à outils',        // Tool box

        ];

        // realistic conditions
        $toolConditions = ["vieux", "neuf", "bon", "reconditioné"];
        
        // conditional per category that is linked to right category Entity
        for ($i = 0; $i < 30; $i++){

            $tool = new Tool();
            $tool->setName($electromenagerTools[mt_rand(0,14)]);
            $tool->setOwner($users[mt_rand(0, count($users)-1)]);
            $tool->setToolCategory($electromenagerCategory);
            $tool->setPriceDay(mt_rand(0,10));
            $tool->setDescription($faker->paragraph(1));
            $tool->setToolCondition($toolConditions[mt_rand(0, count($toolConditions)-1)]);
            $tool->setImageTool('https://res.cloudinary.com/dzqge7ico/image/upload/v1738328316/ToolSwap_placeholder_cxpuyz.webp');

            $manager->persist($tool);
        }

        for ($i = 0; $i < 30; $i++){


            $tool = new Tool();
            $tool->setName($jardinageTools[mt_rand(0,14)]);
            $tool->setOwner($users[mt_rand(0, count($users)-1)]);
            $tool->setToolCategory($jardinageCategory);
            $tool->setPriceDay(mt_rand(0,10));
            $tool->setDescription($faker->paragraph(1));
            $tool->setToolCondition($toolConditions[mt_rand(0, count($toolConditions)-1)]);
            $tool->setImageTool('https://res.cloudinary.com/dzqge7ico/image/upload/v1738328316/ToolSwap_placeholder_cxpuyz.webp');

            $manager->persist($tool);
        }

        for ($i = 0; $i < 30; $i++){

            $tool = new Tool();
            $tool->setName($constructionTools[mt_rand(0,14)]);
            $tool->setOwner($users[mt_rand(0, count($users)-1)]);
            $tool->setToolCategory($constructionCategory);
            $tool->setDescription($faker->paragraph(1));
            $tool->setPriceDay(mt_rand(0,10));
            $tool->setToolCondition($toolConditions[mt_rand(0, count($toolConditions)-1)]);
            $tool->setImageTool('https://res.cloudinary.com/dzqge7ico/image/upload/v1738328316/ToolSwap_placeholder_cxpuyz.webp');

            $manager->persist($tool);
        }

        // User1 tools x 8 for testing
        for ($i = 0; $i < 8; $i++){


            $tool = new Tool();
            $tool->setName($constructionTools[mt_rand(0,14)]);
            $tool->setOwner($users[0]);
            $tool->setToolCategory($constructionCategory);
            $tool->setPriceDay(mt_rand(0,2));
            $tool->setDescription('This is a test tool.');
            $tool->setToolCondition('neuf');
            $tool->setImageTool('https://res.cloudinary.com/dzqge7ico/image/upload/v1738328316/ToolSwap_placeholder_cxpuyz.webp');
            
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
