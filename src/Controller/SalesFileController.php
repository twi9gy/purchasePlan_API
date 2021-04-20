<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\SalesFile;
use App\Entity\User;
use App\Model\SalesFileDto;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use OpenApi\Annotations as OA;

/**
 * @Route("/api/v1/sales_file")
 */
class SalesFileController extends AbstractController
{
    /**
     *
     * @OA\Get(
     *     path="/api/v1/sales_file/",
     *     tags={"sales file"},
     *     summary="Получение всех файлов продаж пользователя.",
     *     description="Данный метод доступен только авторизованным пользователям.",
     *     operationId="file.sales",
     *     @OA\Response(
     *          response="200",
     *          description="Успешная операция",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="fileSales",
     *                  type="array",
     *                  @OA\Items(
     *                      @OA\Property(
     *                          property="id",
     *                          type="integer"
     *                      ),
     *                      @OA\Property(
     *                          property="filename",
     *                          type="string"
     *                      )
     *                  ),
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
     *     )
     * )
     *
     * @Route("/", name="sales_file_index", methods={"GET"})
     * @param Request $request
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function index(Request $request, SerializerInterface $serializer): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $userRepository = $entityManager->getRepository(User::class);
        $categoryRepository = $entityManager->getRepository(Category::class);
        $salesFileRepository = $entityManager->getRepository(SalesFile::class);

        $response = new Response();

        // Получаем текущего пользователя
        $userToken = $this->getUser();
        $user = $userRepository->findOneBy(['email' => $userToken->getUsername()]);

        if ($user) {
            if ($request->get('category_id') !== null) {
                $category_id = $request->get('category_id');
                // Получаем категорию
                $category = $categoryRepository->find($category_id);
                if ($category) {
                    // Получаем все файлы категории
                    $files = $salesFileRepository->findBy(['category' => $category_id]);

                    // Формируем массив категорий
                    $result = [];
                    foreach ($files as $file) {
                        $result[] = [
                            'id' => $file->getId(),
                            'filename' => $file->getFilename(),
                        ];
                    }

                    // Формируем ответ сервера
                    $data = [
                        "code" => Response::HTTP_OK,
                        "files" => $result
                    ];
                    $response->setStatusCode(Response::HTTP_OK);
                } else {
                    // Формируем ответ сервера
                    $data = [
                        "code" => Response::HTTP_BAD_REQUEST,
                        "message" => 'Каталог не найден'
                    ];
                    $response->setStatusCode(Response::HTTP_BAD_REQUEST);
                }
            } else {
                // Получаем все файлы категории
                $files = $salesFileRepository->findBy(['purchase_user' => $user->getId()]);

                // Формируем массив категорий
                $result = [];
                foreach ($files as $file) {
                    $result[] = [
                        'id' => $file->getId(),
                        'filename' => $file->getFilename(),
                    ];
                }

                // Формируем ответ сервера
                $data = [
                    "code" => Response::HTTP_OK,
                    "files" => $result
                ];
                $response->setStatusCode(Response::HTTP_OK);
            }
        } else {
            // Формируем ответ сервера
            $data = [
                "code" => Response::HTTP_UNAUTHORIZED,
                "message" => 'Пользователь не авторизован'
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
     *     path="/api/v1/sales_file/new",
     *     tags={"sales file"},
     *     summary="Создание нового файла продаж.",
     *     description="Данный метод доступен только авторизованным пользователям.",
     *     operationId="sales_file.new",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  type="object",
     *                  @OA\Property(
     *                      property="file",
     *                      type="string",
     *                      format="binary"
     *                  ),
     *                  @OA\Property(
     *                      property="filename",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="category_id",
     *                      type="integer"
     *                  ),
     *              )
     *         )
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
     *                  property="file_id",
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
     * @Route("/new", name="sales_file_new", methods={"POST"})
     * @param ValidatorInterface $validator
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function new(ValidatorInterface $validator, SerializerInterface $serializer): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $categoryRepository = $entityManager->getRepository(Category::class);
        $userRepository = $entityManager->getRepository(User::class);

        $response = new Response();

        $fileDto = new SalesFileDto();
        $fileDto->filename = $_POST['filename'];
        $fileDto->category_id = $_POST['category_id'];
        // Проверка ошибок валидации
        $errors = $validator->validate($fileDto);

        if (count($errors) > 0) {
            // Формируем ответ сервера
            $data = [
                "code" => Response::HTTP_BAD_REQUEST,
                "message" => $errors
            ];
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
        } else {
            $uploadResult = $this->uploadFile($fileDto->filename);

            if ($uploadResult['code'] === Response::HTTP_OK) {
                // Создание объекта класса Category из Dto
                $file = SalesFile::fromDto($fileDto, $categoryRepository);
                $file->setCreatedAt(new \DateTime());
                $file->setEditAt($file->getCreatedAt());
                // Получаем пользователя
                $user = $userRepository->findOneBy(['email' => $this->getUser()->getUsername()]);
                // Устанавливаем файлу пользователя
                $file->setPurchaseUser($user);

                // Сохраняем файл
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($file);
                $entityManager->flush();

                // Формируем ответ сервера
                $data = [
                    "code" => Response::HTTP_OK,
                    "file_id" => $file->getId(),
                    "message" => 'Файл был успешно создан.'
                ];
                $response->setStatusCode(Response::HTTP_OK);
            } else {
                $data = $uploadResult;
            }
        }

        $response->setContent($serializer->serialize($data, 'json'));
        $response->headers->add(['Content-Type' => 'application/json']);
        return $response;
    }

    /**
     *
     * @OA\Get(
     *     path="/api/v1/sales_file/{id}",
     *     tags={"sales file"},
     *     summary="Получение файла продаж пользователя.",
     *     description="Данный метод доступен только авторизованным пользователям.",
     *     operationId="categories.getSaleFileById",
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
     * @Route("/{id}", name="sales_file_show", methods={"GET"})
     * @param SalesFile $salesFile
     * @return Response
     */
    public function show(SalesFile $salesFile): Response
    {
        return $this->render('sales_file/show.html.twig', [
            'sales_file' => $salesFile,
        ]);
    }

    /**
     *
     * @OA\Post(
     *     path="/api/v1/sales_file/{id}/edit",
     *     tags={"sales file"},
     *     summary="Редактирование названия файла продаж.",
     *     description="Данный метод доступен только авторизованным пользователям.",
     *     operationId="sales_file.edit",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/SalesFileDto")
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
     * @Route("/{id}/edit", name="sales_file_edit", methods={"POST"})
     * @param Request $request
     * @param SalesFile $salesFile
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validator
     * @return Response
     */
    public function edit(
        Request $request,
        SalesFile $salesFile,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ): Response {
        // Десериализация запроса в Dto
        $fileDto = $serializer->deserialize($request->getContent(), SalesFileDto::class, 'json');
        // Проверка ошибок валидации
        $errors = $validator->validate($fileDto);

        $response = new Response();

        if (count($errors) > 0) {
            // Формируем ответ сервера
            $data = [
                "code" => Response::HTTP_BAD_REQUEST,
                "message" => $errors
            ];
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
        } else {
            $salesFile->setFilename($fileDto->filename);
            $salesFile->setEditAt(new \DateTime());

            // Сохраняем файл
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($salesFile);
            $entityManager->flush();

            // Формируем ответ сервера
            $data = [
                "code" => Response::HTTP_OK,
                "message" => 'Название файла было успешно изменено.'
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
     *     path="/api/v1/sales_file/{id}",
     *     tags={"sales file"},
     *     summary="Удаление файла продаж.",
     *     description="Данный метод доступен только авторизованным пользователям.",
     *     operationId="sales_file.delete",
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
     * @Route("/{id}", name="sales_file_delete", methods={"DELETE"})
     * @param SalesFile $salesFile
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function delete(SalesFile $salesFile, SerializerInterface $serializer): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $userRepository = $entityManager->getRepository(User::class);
        $salesFileRepository = $entityManager->getRepository(SalesFile::class);

        $response = new Response();

        $userJwt = $this->getUser();
        $user = $userRepository->findOneBy(['email' => $userJwt->getUsername()]);
        if ($salesFileRepository->findOneBy([
            'purchase_user' => $user->getId(),
                'filename' => $salesFile->getFilename()])) {
            // Удаляем файл из файловой системы
            // Путь до директории для загрузки
            $basePath = $this->getParameter('kernel.project_dir')
                . '/public/uploads/userFiles/' . $userJwt->getUsername();

            if (is_file($basePath . '/' . $salesFile->getFilename())) {
                unlink($basePath . '/' . $salesFile->getFilename());
            }

            // Удаляем запись о файле из БД
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($salesFile);
            $entityManager->flush();

            // Формируем ответ сервера
            $data = [
                "code" => Response::HTTP_OK,
                "message" => "Файл был удален."
            ];
            $response->setStatusCode(Response::HTTP_OK);
        } else {
            // Формируем ответ сервера
            $data = [
                "code" => Response::HTTP_BAD_REQUEST,
                "message" => "Вы не являетесь владельцем файла продаж."
            ];
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
        }

        $response->setContent($serializer->serialize($data, 'json'));
        return $response;
    }

    /***
     * @param string $filename
     * @return array
     */
    public function uploadFile(string $filename) : array
    {
        $userJWT = $this->getUser();

        if ($userJWT) {
            if (isset($_FILES['file'])) {
                // Переданный массив сохраняем в переменной
                $file = $_FILES['file'];

                // Проверяем размер файла и если он превышает заданный размер
                // завершаем выполнение скрипта и выводим ошибку
                if ($file['size'] > 2000000) {
                    // Формируем ответ сервера
                    $data = [
                        "code" => Response::HTTP_CONFLICT,
                        "message" => "Файл больше чем 20 мб"
                    ];
                } else {
                    // Достаем формат файла
                    $fileFormat = explode('.', $file['name']);

                    // Сохраняем тип изображения в переменную
                    $fileType = $file['type'];

                    // Путь до директории для загрузки
                    $basePath = $this->getParameter('kernel.project_dir')
                        . '/public/uploads/userFiles/' . $userJWT->getUsername();

                    if (!is_dir($basePath) && !mkdir($basePath, 0777, true) && !is_dir($basePath)) {
                        throw new \RuntimeException(sprintf('Directory "%s" was not created', $basePath));
                    }

                    if (move_uploaded_file($file['tmp_name'], $basePath . '/' . $filename)) {
                        $data = [
                            "code" => Response::HTTP_OK,
                            "message" => "Файл загружен"
                        ];
                    } else {
                        $data = [
                            "code" => Response::HTTP_CONFLICT,
                            "message" => "Файл не удалось загрузить"
                        ];
                    }
                }
            } else {
                // Формируем ответ сервера
                $data = [
                    "code" => Response::HTTP_CONFLICT,
                    "message" => "Сервер не получил файл"
                ];
            }
        } else {
            // Формируем ответ сервера
            $data = [
                "code" => Response::HTTP_CONFLICT,
                "message" => "Пользователь не автроризован"
            ];
        }

        return $data;
    }
}
