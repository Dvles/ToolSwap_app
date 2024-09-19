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

        // 'users' to test web application
        for ($i = 1; $i < 6; $i++){
            $user = new User();
            $user->setEmail("user" . $i . "@gmail.com");
            $user->setFirstName($fakerBe->firstName());
            $user->setLastName($fakerBe->lastName());
            $hashedPassword = $this->passwordHasher->hashPassword($user, '0000');
            $user->setPassword($hashedPassword);
            $user->setRoles(['ROLE_USER']);
            $user->setCommunity("Ixelles");
            $manager->persist($user);
        }

        // 'realistic users' for display purposes 
        // conditional to generate diverse country with multiple nationalities
        for ($i = 1; $i < 50; $i++){
            if($i % 5 === 0 && $i % 7 !== 0 && $i % 11 !== 0){
                $firstName = $fakerNl->firstName();
                $lastName = $fakerNl->lastName();
                $email = ($firstName . '.' . $lastName . $i . "@gmail.com");
            } elseif ($i % 7 === 0){
                $firstName = $fakerRw->firstName();
                $lastName = $fakerRw->lastName();
                $email = ($firstName . '.' . $lastName . $i . "@gmail.com");
            } elseif ($i % 11 === 0 ) {
                $firstName = $fakerIt->firstName();
                $lastName = $fakerIt->lastName();
                $email = ($firstName . '.' . $lastName . $i . "@gmail.com");
            } else {
                $firstName = $fakerBe->firstName();
                $lastName = $fakerBe->lastName();
                $email = ($firstName . '.' . $lastName . $i . "@gmail.com");
            }

            $email = strtolower($email);
            $user = new User();
            $user->setEmail($email);
            $user->setFirstName($firstName);
            $user->setLastName($lastName);
            $hashedPassword = $this->passwordHasher->hashPassword($user, '0000');
            $user->setPassword($hashedPassword);
            $user->setRoles(['ROLE_USER']);
            $user->setCommunity($fakerBe->city());
            $manager->persist($user);
        }

        $manager->flush();
    }
}
