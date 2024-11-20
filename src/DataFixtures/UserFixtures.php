<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private $passwordHasher;
    
    public function __construct(UserPasswordHasherInterface $passwordHasher){
        $this->passwordHasher = $passwordHasher;
    }
    
    public function load(ObjectManager $manager): void
    {

        // Create realistic user data using multiple locales
        $fakerBe = Factory::create("fr_BE");
        $fakerRw = Factory::create("rw_RW");
        $fakerIt = Factory::create("it_IT");
        $fakerNl = Factory::create("nl_BE");
        $bxlMunicipalities = [
            "Ixelles",        
            "Schaerbeek",     
            "Woluwe-saint-lambert", 
            "Woluwe-saint-pierre",  
            "Bruxelles-centre",     
            "Anderlecht",    
            "Etterbeke",    
            "Forest",        
            "Molenbeek-saint-jean", 
            "Saint-gilles",  
            "Saint-josse-ten-noode", 
            "Uccle"       
        ];


        // 'users' to test web application
        for ($i = 1; $i < 6; $i++){
            $user = new User();
            $user->setEmail("user" . $i . "@gmail.com");
            $user->setFirstName($fakerBe->firstName());
            $user->setLastName($fakerBe->lastName());
            $hashedPassword = $this->passwordHasher->hashPassword($user, '0000');
            $user->setPassword($hashedPassword);
            $user->setRoles(['ROLE_USER']);
            $user->setCommunity("ixelles");
            
            $manager->persist($user);

            // Set the reference for user1
            $this->addReference('user' . $i, $user); 
        }

        // 'realistic users' for display purposes 
        // conditional to generate diverse country with multiple nationalities
        for ($i = 1; $i < 150; $i++){
            if($i % 5 === 0 && $i % 7 !== 0 && $i % 11 !== 0){
                $firstName = $fakerNl->firstName();
                $lastName = $fakerNl->lastName();
                $email = ($firstName . '.' . $lastName . $i . "@gmail.com");
                $city = $bxlMunicipalities[mt_rand(0,12)];
            } elseif ($i % 7 === 0){
                $firstName = $fakerRw->firstName();
                $lastName = $fakerRw->lastName();
                $email = ($firstName . '.' . $lastName . $i . "@gmail.com");
                $city = $bxlMunicipalities[mt_rand(0,12)];

            } elseif ($i % 11 === 0 ) {
                $firstName = $fakerIt->firstName();
                $lastName = $fakerIt->lastName();
                $email = ($firstName . '.' . $lastName . $i . "@gmail.com");
                $city = $bxlMunicipalities[mt_rand(0,12)];
            } else {
                $firstName = $fakerBe->firstName();
                $lastName = $fakerBe->lastName();
                $email = ($firstName . '.' . $lastName . $i . "@gmail.com");
                $city = $bxlMunicipalities[mt_rand(0,12)];
            }

            // email treatment
            $email = strtolower($email);
            $cleanEmail = str_replace(' ', '', $email);



            $user = new User();
            $user->setEmail($cleanEmail);
            $user->setFirstName($firstName);
            $user->setLastName($lastName);
            $hashedPassword = $this->passwordHasher->hashPassword($user, '0000');
            $user->setPassword($hashedPassword);
            $user->setRoles(['ROLE_USER']);
            $user->setCommunity($city);
            $user->setImage('https://via.placeholder.com/200x200');
            $manager->persist($user);
        }

        $manager->flush();
    }
}
