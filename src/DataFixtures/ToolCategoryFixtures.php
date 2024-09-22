<?php

namespace App\DataFixtures;

use App\Entity\ToolCategory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ToolCategoryFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Set Categories for realistic web app demo
        $toolCategories = ['Électroménager', 'Jardinage', 'Construction'];
        $toolCatDescriptions = [
            'Appareils utilisés pour les tâches domestiques et les soins personnels',
            'Outils pour l\'entretien des espaces verts et les travaux de jardinage',
            'Outils utilisés pour les travaux de construction et de rénovation',
        ];
        
        for ($i = 0; $i < 3; $i++){
            $toolCategory = new ToolCategory();
            $toolCategory->setName($toolCategories[$i]);
            $toolCategory->setDescription($toolCatDescriptions[$i]);
            $manager->persist($toolCategory);
        }

        $manager->flush();
    }
}
