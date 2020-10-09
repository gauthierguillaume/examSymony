<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Faker\Factory;
use App\Entity\User;
class UserFixtures extends Fixture
{

    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }
    public function load(ObjectManager $manager)
    {
        // $product = new Product();
        // $manager->persist($product);
        $faker = Factory::create("FR-fr");
        $user = new User();
        $user->setEmail('gaston@gaston.fr')
            ->setPassword($this->passwordEncoder->encodePassword(
                $user,
                'gastonpass'
            ))
            ->setRoles(['ROLE_ADMIN']);
            $manager->persist($user);  
        $user2 = new User();
        $user2->setEmail('jeanjean@jeanjean.fr')
            ->setPassword($this->passwordEncoder->encodePassword(
                $user2,
                'jeanjeanpass'
            ))
            ->setRoles(['ROLE_USER']);
        $manager->persist($user2);
        $manager->flush();
    }
}