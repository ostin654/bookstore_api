<?php

namespace App\Command;

use App\Entity\Author;
use App\Entity\AuthorTranslation;
use App\Entity\Book;
use App\Entity\BookTranslation;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DatabaseFillCommand extends Command
{
    protected static $defaultName = 'database:fill';
    private EntityManagerInterface $entityManager;

    public function __construct(string $name = null, EntityManagerInterface $entityManager)
    {
        parent::__construct($name);
        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this
            ->setDescription('Fill database with fake data')
            ->addOption('authorCount', null, InputOption::VALUE_REQUIRED)
            ->addOption('bookCount', null, InputOption::VALUE_REQUIRED)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);
        $authorCount = $input->getOption('authorCount');
        $bookCount = $input->getOption('bookCount');

        $symfonyStyle->title('Database filling');

        $ruFaker = Factory::create('ru_RU');
        $enFaker = Factory::create('en_US');

        $symfonyStyle->section('Filling authors');

        $progressBar = new ProgressBar($output);
        $progressBar->setMaxSteps($authorCount);
        $progressBar->start();

        $authors = [];
        for ($i = 0; $i < $authorCount; $i++) {
            $author = new Author();
            $author->translate('ru')->setName($ruFaker->name);
            $author->translate('en')->setName($enFaker->name);
            $this->entityManager->persist($author);
            $author->mergeNewTranslations();

            $authors[] = $author;

            $this->entityManager->flush();
            $this->entityManager->clear();

            $progressBar->advance();
        }

        $progressBar->finish();
        $symfonyStyle->writeln('');
        $symfonyStyle->writeln('');

        $symfonyStyle->section('Filling books');

        $progressBar->setMaxSteps($bookCount);
        $progressBar->start();

        for ($i = 0; $i < $bookCount; $i++) {
            $book = new Book();
            $book->translate('ru')->setName($ruFaker->sentence(4));
            $book->translate('en')->setName($enFaker->sentence(4));

            $randomAuthorIds = array_unique((array)array_rand($authors, mt_rand(1, 4)));
            foreach ($randomAuthorIds as $authorId) {
                $bookAuthor = $this->entityManager->getRepository(Author::class)
                    ->find($authors[$authorId]->getId());
                $book->getAuthors()->add($bookAuthor);
            }

            $this->entityManager->persist($book);
            $book->mergeNewTranslations();

            $this->entityManager->flush();
            $this->entityManager->clear();

            $progressBar->advance();
        }

        $progressBar->finish();
        $symfonyStyle->writeln('');
        $symfonyStyle->writeln('');

        return Command::SUCCESS;
    }
}
