<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\User;
use App\Model\CategoryDto;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use OpenApi\Annotations as OA;

/**
 * @Route("/api/v1/categories")
 */
class CategoryController extends AbstractController
{
    /**
     *
     * @OA\Get(
     *     path="/api/v1/categories/",
     *     tags={"category"},
     *     summary="Получение всех категорий пользователя.",
     *     description="Данный метод доступен только авторизованным пользователям.",
     *     operationId="categories",
     *     @OA\Response(
     *          response="200",
     *          description="Успешная операция",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="categories",
     *                  type="array",
     *                  @OA\Items(
     *                      @OA\Property(
     *                          property="id",
     *                          type="integer"
     *                      ),
     *                      @OA\Property(
     *                          property="name",
     *                          type="string"
     *                      )
     *                  ),
     *              )
     *          )
     *     ),
     *     @OA\Response(
     *          response="401",
     *          description="Неавторизованынй пользователь.",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="code",
     *                  type="string",
     *                  example="401"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  example="JWT Token not found"
     *              )
     *          )
     *     )
     * )
     *
     * @Route("/", name="categories_index", methods={"GET"})
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function allCategories(SerializerInterface $serializer): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $userRepository = $entityManager->getRepository(User::class);
        $categoryRepository = $entityManager->getRepository(Category::class);


        // Получаем текущего пользователя
        $userJwt = $this->getUser();
        $user = $userRepository->findOneBy(['email' => $userJwt->getUsername()]);

        $response = new Response();

        if ($user) {
            // Получаем категории пользователя
            $categories = $categoryRepository->findBy(['purchase_user' => $user->getId()]);

            // Формируем массив категорий
            $result = [];
            foreach ($categories as $category) {
                $result[] = [
                    'id' => $category->getId(),
                    'name' => $category->getName()
                ];
            }

            // Устанавливаем ответ сервера
            $data = [
                'code' => Response::HTTP_OK,
                'categories' => $result
            ];
            $response->setStatusCode(Response::HTTP_OK);
        } else {
            // Устанавливаем ответ сервера
            $data = [
                'code' => Response::HTTP_UNAUTHORIZED,
                'message' => 'Пользователь не авторизован'
            ];
            $response->setStatusCode(Response::HTTP_UNAUTHORIZED);
        }
        $response->setContent($serializer->serialize($data, 'json'));
        $response->headers->add(['Content-Type' => 'application/json']);
        return $response;
    }

    /**
     *
     * @OA\Post(
     *     path="/api/v1/categories/new",
     *     tags={"category"},
     *     summary="Создание новой категории.",
     *     description="Данный метод доступен только авторизованным пользователям.",
     *     operationId="categories.new",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CategoryDto")
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="Успешная операция",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="code",
     *                  type="integer",
     *                  example="200"
     *              ),
     *              @OA\Property(
     *                  property="id",
     *                  type="integer"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *     ),
     *     @OA\Response(
     *          response="400",
     *          description="Внутренняя ошибка.",
     *          @OA\JsonContent(ref="#/components/schemas/FailResponse")
     *     ),
     *     @OA\Response(
     *          response="401",
     *          description="Неавторизованынй пользователь.",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="code",
     *                  type="string",
     *                  example="401"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  example="JWT Token not found"
     *              )
     *          )
     *     ),
     * )
     *
     * @Route("/new", name="categories_new", methods={"POST"})
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validator
     * @return Response
     */
    public function new(
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ): Response {
        $entityManager = $this->getDoctrine()->getManager();
        $userRepository = $entityManager->getRepository(User::class);

        $response = new Response();

        // Десериализация запроса в Dto
        $categoryDto = $serializer->deserialize($request->getContent(), CategoryDto::class, 'json');
        // Проверка ошибок валидации
        $errors = $validator->validate($categoryDto);

        if (count($errors) > 0) {
            // Формируем ответ сервера
            $data = [
                "code" => Response::HTTP_BAD_REQUEST,
                "message" => $errors
            ];
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
        } else {
            // Создание объекта класса Category из Dto
            $category = Category::fromDto($categoryDto);
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
                $response->setStatusCode(Response::HTTP_OK);
            } else {
                // Формируем ответ сервера
                $data = [
                    "code" => Response::HTTP_UNAUTHORIZED,
                    "message" => 'Пользователь не найден.'
                ];
                $response->setStatusCode(Response::HTTP_UNAUTHORIZED);
            }
        }

        $response->setContent($serializer->serialize($data, 'json'));
        $response->headers->add(['Content-Type' => 'application/json']);
        return $response;
    }

    /**
     *
     * @OA\Get(
     *     path="/api/v1/categories/{id}",
     *     tags={"category"},
     *     summary="Получение категории пользователя.",
     *     description="Данный метод доступен только авторизованным пользователям.",
     *     operationId="categories.getCategoryById",
     *     @OA\Response(
     *          response="200",
     *          description="Успешная операция",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="code",
     *                  type="integer",
     *                  example="200"
     *              ),
     *              @OA\Property(
     *                  property="id",
     *                  type="integer"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *     ),
     *     @OA\Response(
     *          response="400",
     *          description="Внутренняя ошибка.",
     *          @OA\JsonContent(ref="#/components/schemas/FailResponse")
     *     )
     * )
     *
     * @Route("/{id}", name="categories_show", methods={"GET"})
     * @param Category $category
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function getCategoryByID(Category $category, SerializerInterface $serializer): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $categoryRepository = $entityManager->getRepository(Category::class);

        $response = new Response();

        // Надо var_dump посмотреть $category
        $categoryRes = $categoryRepository->findOneBy(['id' => $category->getId()]);

        if ($categoryRes) {
            // Формируем ответ сервера
            $data = [
                "code" => Response::HTTP_OK,
                "id" => $category->getId(),
                "name" => $categoryRes->getName()
            ];
            $response->setStatusCode(Response::HTTP_OK);
        } else {
            // Формируем ответ сервера
            $data = [
                "code" => Response::HTTP_BAD_REQUEST,
                "message" => 'Категории с Id = ' . $category->getId() . ' не существует.'
            ];
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
        }

        $response->setContent($serializer->serialize($data, 'json'));
        $response->headers->add(['Content-Type' => 'application/json']);
        return $response;
    }

    /**
     *
     * @OA\Post(
     *     path="/api/v1/categories/{id}/edit",
     *     tags={"category"},
     *     summary="Редактирование категории.",
     *     description="Данный метод доступен только авторизованным пользователям.",
     *     operationId="categories.edit",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CategoryDto")
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="Успешная операция",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="code",
     *                  type="integer",
     *                  example="200"
     *              ),
     *              @OA\Property(
     *                  property="id",
     *                  type="integer"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *     ),
     *     @OA\Response(
     *          response="400",
     *          description="Внутренняя ошибка.",
     *          @OA\JsonContent(ref="#/components/schemas/FailResponse")
     *     )
     * )
     *
     * @Route("/{id}/edit", name="categories_edit", methods={"POST"})
     * @param Request $request
     * @param Category $category
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validator
     * @return Response
     */
    public function edit(
        Request $request,
        Category $category,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ): Response {
        // Десериализация запроса в Dto
        $categoryDto = $serializer->deserialize($request->getContent(), CategoryDto::class, 'json');
        // Проверка ошибок валидации
        $errors = $validator->validate($categoryDto);

        $response = new Response();

        if (count($errors) > 0) {
            // Формируем ответ сервера
            $data = [
                "code" => Response::HTTP_BAD_REQUEST,
                "message" => $errors
            ];
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
        } else {
            $category->setName($categoryDto->name);
            // Сохраняем категорию
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($category);
            $entityManager->flush();
            // Формируем ответ сервера
            $data = [
                "code" => Response::HTTP_OK,
                "message" => "Название категории было изменено."
            ];
            $response->setStatusCode(Response::HTTP_OK);
        }

        $response->setContent($serializer->serialize($data, 'json'));
        $response->headers->add(['Content-Type' => 'application/json']);
        return $response;
    }

    /**
     *
     * @OA\Delete(
     *     path="/api/v1/categories/{id}",
     *     tags={"category"},
     *     summary="Удаление категории.",
     *     description="Данный метод доступен только авторизованным пользователям.",
     *     operationId="categories.delet",
     *     @OA\Response(
     *          response="200",
     *          description="Успешная операция",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="code",
     *                  type="integer",
     *                  example="200"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *     ),
     *     @OA\Response(
     *          response="400",
     *          description="Внутренняя ошибка.",
     *          @OA\JsonContent(ref="#/components/schemas/FailResponse")
     *     )
     * )
     *
     *
     * @Route("/{id}", name="categories_delete", methods={"DELETE"})
     * @param Category $category
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function delete(Category $category, SerializerInterface $serializer): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $userRepository = $entityManager->getRepository(User::class);
        $categoryRepository = $entityManager->getRepository(Category::class);

        $response = new Response();

        $userJwt = $this->getUser();
        $user = $userRepository->findOneBy(['email' => $userJwt->getUsername()]);
        if ($categoryRepository->findOneBy(['purchase_user' => $user->getId(),'name' => $category->getName()])) {
            // Удаляем кетегорию
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($category);
            $entityManager->flush();

            // Формируем ответ сервера
            $data = [
                "code" => Response::HTTP_OK,
                "message" => "Категория " . $category->getName() . " была удалена"
            ];
            $response->setStatusCode(Response::HTTP_OK);
        } else {
            // Формируем ответ сервера
            $data = [
                "code" => Response::HTTP_BAD_REQUEST,
                "message" => "Вы не являетесь владельцем категории."
            ];
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
        }

        $response->setContent($serializer->serialize($data, 'json'));
        $response->headers->add(['Content-Type' => 'application/json']);
        return $response;
    }
}
