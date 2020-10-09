<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Media;
use App\Entity\Commune;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create("FR-fr");
        // $product = new Product();
        // $manager->persist($product);
        for ($i = 0; $i < 20; $i++) {
            $values = [($faker->numberBetween(500, 40000))];     
            $commune = new Commune();
            $ImageCom = new Media;
            $commune->setNom($faker->state)
                ->setCode($faker->postcode)
                ->setPopulation($faker->numberBetween(9000, 70000))
                ->setCodeDepartement($faker->numberBetween(01, 99))
                ->setCodeRegion($faker->numberBetween(01, 50))
                ->setCodesPostaux($faker->randomElement($array = array($values)));
            $ImageCom->setImage($faker->imageUrl(688, 488, 'city'))
                ->setVideo($faker->imageUrl(688, 488, 'city'))
                ->setArticle($faker->text)
                ->setCommune($commune);
            $manager->persist($commune);
            $manager->persist($ImageCom);
        }

        $manager->flush();
    }
}