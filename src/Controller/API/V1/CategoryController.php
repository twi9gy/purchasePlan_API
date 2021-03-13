<?php

namespace App\Controller\API\V1;

use App\Entity\Category;
use App\Model\Request\CategoryDtoRequest;
use App\Repository\CategoryRepository;
use App\Repository\UserRepository;
use JMS\Serializer\SerializerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;



/**
 * @Route("/api/v1/category")
 */
class CategoryController extends AbstractController
{
    /**
     * @Route("/all", name="category_index", methods={"GET"})
     * @param \App\Repository\CategoryRepository $categoryRepository
     * @param \JMS\Serializer\SerializerInterface $serializer
     * @param UserRepository $userRepository
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function allCategories(CategoryRepository $categoryRepository,
                                  SerializerInterface $serializer, UserRepository $userRepository): Response
    {
        // Получаем текущего пользователя
        $userJwt = $this->getUser();
        $user = $userRepository->findOneBy(['email' => $userJwt->getUsername()]);
        if ($user) {
            // Получаем категории пользователя
            $categories = $categoryRepository->findByUserId($user->getId());

            // Формируем массив категорий
            $result = [];
            foreach ($categories as $category) {
                $result[] = [
                    'id' => $category->getId(),
                    'name' => $category->getName()
                ];
            }

            // Определяем общее количество категорий у пользователя (для пагинации)
            $totalCount = $categoryRepository->findBy(['user_id' => $user->getId()]);

            // Устанавливаем ответ сервера
            $data = [
                'code' => Response::HTTP_OK,
                'categories' => $result
            ];
        } else {
            // Устанавливаем ответ сервера
            $data = [
                'code' => Response::HTTP_CONFLICT,
                'message' => 'Пользователь не авторизован'
            ];
        }

        $response = new Response();
        $response->setContent($serializer->serialize($data, 'json'));
        if ($data['code'] === Response::HTTP_OK) {
            $response->setStatusCode(Response::HTTP_OK);
        } else {
            $response->setStatusCode(Response::HTTP_CONFLICT);
        }
        return $response;
    }

    /**
     * @Route("/new", name="category_new", methods={"POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \JMS\Serializer\SerializerInterface $serializer
     * @param \Symfony\Component\Validator\Validator\ValidatorInterface $validator
     * @param \App\Repository\CategoryRepository $categoryRepository
     * @param \App\Repository\UserRepository $userRepository
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function new(Request $request, SerializerInterface $serializer,
                        ValidatorInterface $validator, CategoryRepository $categoryRepository,
                        UserRepository $userRepository): Response
    {
        // Десериализация запроса в Dto
        $categoryDto = $serializer->deserialize($request->getContent(), CategoryDtoRequest::class, 'json');
        // Проверка ошибок валидации
        $errors = $validator->validate($categoryDto);

        if (count($errors) > 0) {
            // Формируем ответ сервера
            $data = [
                "code" => Response::HTTP_CONFLICT,
                "message" => $errors
            ];
        } else {
            // Создание объекта класса Category из Dto
            $category = \App\Entity\Category::fromDto($categoryDto);
            // Получаем текущего пользователя
            $email = $this->getUser()->getUsername();
            $user = $userRepository->findOneBy(['email' => $email]);

            if ($user !== null) {
                // Присваиваем пользователю категорию
                $user->addCategory($category);

                // Сохраняем категорию
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($category);
                $entityManager->flush();

                // Формируем ответ сервера
                $data = [
                    "code" => Response::HTTP_OK,
                    "id" => $category->getId(),
                    "message" => 'Категория ' . $category->getName() . ' была создана'
                ];
            } else {
                // Формируем ответ сервера
                $data = [
                    "code" => Response::HTTP_FORBIDDEN,
                    "message" => 'User not found.'
                ];
            }
        }

        $response = new Response();
        $response->setContent($serializer->serialize($data, 'json'));
        if ($data['code'] === Response::HTTP_OK) {
            $response->setStatusCode(Response::HTTP_OK);
        } else {
            $response->setStatusCode(Response::HTTP_CONFLICT);
        }
        return $response;
    }

    /**
     * @Route("/{id}", name="category_show", methods={"GET"})
     * @param \App\Entity\Category $category
     * @param \JMS\Serializer\SerializerInterface $serializer
     * @param \App\Repository\CategoryRepository $categoryRepository
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getCategoryByID(Category $category, SerializerInterface $serializer,
                                    CategoryRepository $categoryRepository): Response
    {
        // Надо var_dump посмотреть $category
        $categoryRes = $categoryRepository->findOneBy(['id' => $category->getId()]);

        if ($categoryRes) {
            // Формируем ответ сервера
            $data = [
                "code" => Response::HTTP_OK,
                "id" => $category->getId(),
                "name" => $categoryRes->getName()
            ];
        } else {
            // Формируем ответ сервера
            $data = [
                "code" => Response::HTTP_CONFLICT,
                "message" => 'Category with Id = ' . $category->getId() . ' not found'
            ];
        }

        $response = new Response();
        $response->setContent($serializer->serialize($data, 'json'));
        if ($data['code'] === Response::HTTP_OK) {
            $response->setStatusCode(Response::HTTP_OK);
        } else {
            $response->setStatusCode(Response::HTTP_CONFLICT);
        }
        return $response;
    }

    /**
     * @Route("/{id}/edit", name="category_edit", methods={"POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\Category $category
     * @param \JMS\Serializer\SerializerInterface $serializer
     * @param \Symfony\Component\Validator\Validator\ValidatorInterface $validator
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function edit(Request $request, Category $category,
                         SerializerInterface $serializer, ValidatorInterface $validator): Response
    {
        // Десериализация запроса в Dto
        $categoryDto = $serializer->deserialize($request->getContent(), CategoryDtoRequest::class, 'json');
        // Проверка ошибок валидации
        $errors = $validator->validate($categoryDto);

        if (count($errors) > 0) {
            // Формируем ответ сервера
            $data = [
                "code" => Response::HTTP_CONFLICT,
                "message" => $errors
            ];
        } else {
            $category->setName($categoryDto->name);
            // Сохраняем категорию
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($category);
            $entityManager->flush();
            // Формируем ответ сервера
            $data = [
                "code" => Response::HTTP_OK,
                "message" => "Name category has been changed."
            ];
        }

        $response = new Response();
        $response->setContent($serializer->serialize($data, 'json'));
        if ($data['code'] === Response::HTTP_OK) {
            $response->setStatusCode(Response::HTTP_OK);
        } else {
            $response->setStatusCode(Response::HTTP_CONFLICT);
        }
        return $response;
    }

    /**
     * @Route("/{id}", name="category_delete", methods={"DELETE"})
     * @param \App\Entity\Category $category
     * @param \JMS\Serializer\SerializerInterface $serializer
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete(Category $category, SerializerInterface $serializer): Response
    {
        // Удаляем кетегорию
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($category);
        $entityManager->flush();

        // Формируем ответ сервера
        $data = [
            "code" => Response::HTTP_OK,
            "message" => "Категория " . $category->getName() . " была удалена"
        ];

        $response = new Response();
        $response->setContent($serializer->serialize($data, 'json'));
        if ($data['code'] === Response::HTTP_OK) {
            $response->setStatusCode(Response::HTTP_OK);
        } else {
            $response->setStatusCode(Response::HTTP_CONFLICT);
        }
        return $response;
    }
}
