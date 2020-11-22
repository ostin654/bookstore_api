<?php

namespace App\DataFixtures;

use App\Entity\Book;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class BookFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $book1 = new Book();
        $book1->translate('ru')->setName('Преступление и наказание');
        $book1->translate('en')->setName('Crime and Punishment');
        $manager->persist($book1);
        $book1->mergeNewTranslations();

        $book2 = new Book();
        $book2->translate('ru')->setName('Евгений Онегин');
        $book2->translate('en')->setName('Eugene Onegin');
        $manager->persist($book2);
        $book2->mergeNewTranslations();

        $manager->flush();
    }
}
