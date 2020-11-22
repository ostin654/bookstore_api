<?php


namespace App\Tests\Repository;


use App\Entity\Book;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class BookRepositoryTest extends KernelTestCase
{
    private $entityManager;

    protected function setUp()
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
    }

    public function testSearchByName(): void
    {
        $books = $this->entityManager->getRepository(Book::class)
            ->searchByName('Crime');

        self::assertIsArray($books);
        self::assertCount(1, $books);
        self::assertEquals('Crime and Punishment', $books[0]->translate('en')->getName());

        $books = $this->entityManager->getRepository(Book::class)
            ->searchByName('Онегин');

        self::assertIsArray($books);
        self::assertCount(1, $books);
        self::assertEquals('Евгений Онегин', $books[0]->translate('ru')->getName());

        $books = $this->entityManager->getRepository(Book::class)
            ->searchByName('Not found');

        self::assertIsArray($books);
        self::assertCount(0, $books);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->entityManager->close();
        $this->entityManager = null;
    }
}