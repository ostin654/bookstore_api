<?php

namespace App\Controller;

use App\Entity\Author;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

class AuthorController extends AbstractController
{
    /**
     * @Route("/author/create", name="author_create", methods={"POST"})
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
            $author = new Author();
            foreach ($data['name'] as $name) {
                $author->translate($name['locale'])->setName($name['value']);
            }
            $entityManager->persist($author);
            $author->mergeNewTranslations();
            $entityManager->flush();

            return $this->json(
                [
                    'status' => Response::HTTP_OK,
                    'success' => "Author added successfully",
                    'author' => $serializer->normalize($author, null, [
                        AbstractNormalizer::ATTRIBUTES => ['id', 'name']
                    ]),
                ],
                Response::HTTP_OK
            );
        }
    }
}
