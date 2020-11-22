<?php

namespace App\Controller;

use App\Entity\Author;
use App\Entity\Book;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

class BookController extends AbstractController
{
    /**
     * @Route("/book/create", name="book_create", methods={"POST"})
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validator
     * @return Response
     */
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ): Response {
        $data = json_decode($request->getContent(), true);

        $constraints = new Assert\Collection([
            'name' => new Assert\Required([
                new Assert\Type('array'),
                new Assert\All([
                    new Assert\Collection([
                        'locale' => [
                            new Assert\NotBlank(),
                            new Assert\Type('string'),
                            new Assert\Choice(['choices' => ['ru', 'en']])
                        ],
                        'value' => [
                            new Assert\NotBlank(),
                            new Assert\Length(['min'=>3, 'max'=>255])
                        ]
                    ])
                ])
            ]),
            'authors' => new Assert\Required([
                new Assert\Type('array'),
                new Assert\Count(['min'=>1]),
                new Assert\All([
                    new Assert\Type('int')
                ])
            ])
        ]);

        $violations = $validator->validate($data, $constraints);

        if (count($violations) > 0) {
            return $this->json(
                [
                    'status' => Response::HTTP_BAD_REQUEST,
                    'error' => 'Input data contains errors',
                    'violations' => $serializer->normalize($violations)['violations'],
                ],
                Response::HTTP_BAD_REQUEST
            );
        } else {
            $book = new Book();
            foreach ($data['name'] as $name) {
                $book->translate($name['locale'])->setName($name['value']);
            }

            $authors = $entityManager->getRepository(Author::class)->findBy(['id' => $data['authors']]);
            foreach ($authors as $author) {
                $book->getAuthors()->add($author);
            }

            $entityManager->persist($book);
            $book->mergeNewTranslations();
            $entityManager->flush();

            return $this->json(
                [
                    'status' => Response::HTTP_OK,
                    'success' => "Book added successfully",
                    'book' => $serializer->normalize($book, null, [
                        AbstractNormalizer::ATTRIBUTES => [
                            'id', 'name', 'authors' => [
                                'id', 'name'
                            ]
                        ]
                    ]),
                ],
                Response::HTTP_OK
            );
        }
    }

    /**
     * @Route(
     *     "/{_locale}/book/{id}",
     *     name="get_book",
     *     methods={"GET"},
     *     requirements={"_locale": "en|ru", "id"="\d+"}
     * )
     * @param int $id
     * @param EntityManagerInterface $entityManager
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function getBook(
        int $id,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer
    ): Response {
        $book = $entityManager->getRepository(Book::class)->find($id);

        if ($book !== null) {
            return $this->json(
                [
                    'status' => Response::HTTP_OK,
                    'book' => $serializer->normalize($book, null, [
                        AbstractNormalizer::ATTRIBUTES => [
                            'id', 'name', 'authors' => [
                                'id', 'name'
                            ]
                        ]
                    ]),
                ],
                Response::HTTP_OK
            );
        } else {
            return $this->json(
                [
                    'status' => Response::HTTP_NOT_FOUND,
                    'error' => 'Book not found'
                ],
                Response::HTTP_NOT_FOUND
            );
        }
    }

    /**
     * @Route(
     *     "/{_locale}/book/search",
     *     name="book_search",
     *     methods={"GET"},
     *     requirements={"_locale": "en|ru"}
     * )
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function search(
        Request $request,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer
    ): Response {
        if ($request->query->has('search')) {
            $books = $entityManager->getRepository(Book::class)
                ->searchByName($request->query->get('search'));

            return $this->json(
                [
                    'status' => Response::HTTP_OK,
                    'books' => $serializer->normalize($books, null, [
                        AbstractNormalizer::ATTRIBUTES => [
                            'id', 'name', 'authors' => [
                                'id', 'name'
                            ]
                        ]
                    ])
                ],
                Response::HTTP_OK
            );
        } else {
            return $this->json(
                [
                    'status' => Response::HTTP_BAD_REQUEST,
                    'error' => 'Required search parameter is missing'
                ],
                Response::HTTP_BAD_REQUEST
            );
        }
    }
}
