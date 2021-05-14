<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\DemandForecastFile;
use App\Entity\SalesFile;
use App\Entity\User;
use App\Exception\DemandForecastServiceException;
use App\Model\DemandForecastFileDto;
use App\Service\CsvService;
use App\Service\DemandForecastService;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use OpenApi\Annotations as OA;

/**
 * @Route("/api/v1/demand_forecast")
 */
class DemandForecastFileController extends AbstractController
{
    /**
     *
     * @OA\Get(
     *     path="/api/v1/demand_forecast/",
     *     tags={"demand forecast"},
     *     summary="Получение всех отчетов о прогнозировании спроса пользователя.",
     *     description="Данный метод доступен только авторизованным пользователям.",
     *     operationId="demandforecast.all",
     *     @OA\Response(
     *          response="200",
     *          description="Успешная операция",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="demandForecastFiles",
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
     * @Route("/", name="demand_forecast_file", methods={"GET"})
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function index(SerializerInterface $serializer): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $demForecastRepository = $entityManager->getRepository(DemandForecastFile::class);
        $userRepository = $entityManager->getRepository(User::class);

        $response = new Response();

        // Получаем пользователя
        $user = $userRepository->findOneBy(['email' => $this->getUser()->getUsername()]);

        if ($user) {
            // Получаем файлы предсказания спроса пользотеля
            $demForecastFiles = $demForecastRepository->findByUser($user->getId());

            // Формируем ответ сервера
            $data = [
                "code" => Response::HTTP_OK,
                "files" => $demForecastFiles
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

        $response->setContent($serializer->serialize($data, 'json'));
        $response->headers->add(['Content-Type' => 'application/json']);
        return $response;
    }

    /**
     *
     * @OA\Post(
     *     path="/api/v1/demand_forecast/new",
     *     tags={"demand forecast"},
     *     summary="Создание нового отчета о пронозировании спроса.",
     *     description="Данный метод доступен только авторизованным пользователям.",
     *     operationId="demand_forecast.new",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/DemandForecastDto")
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
     *     @OA\Response(
     *          response="409",
     *          description="Внутренняя ошибка.",
     *          @OA\JsonContent(ref="#/components/schemas/FailResponse")
     *     )
     * )
     *
     * @Route("/new", name="demand_forecast_file_new", methods={"POST"})
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
        $fileRepository = $entityManager->getRepository(SalesFile::class);
        $categoryRepository = $entityManager->getRepository(Category::class);

        // Десериализация запроса в Dto
        $demandForecastDto = $serializer->deserialize(
            $request->getContent(),
            DemandForecastFileDto::class,
            'json'
        );
        // Проверка ошибок валидации
        $errors = $validator->validate($demandForecastDto);

        $response = new Response();

        if (count($errors) === 0) {
            // Получаем текущего пользователя
            $email = $this->getUser()->getUsername();
            $user = $userRepository->findOneBy(['email' => $email]);

            // Получаем путь до файла
            $pathToFile = $this->getParameter('kernel.project_dir')
                . '/public/uploads/userFiles/' . $user->getUsername();

            // Путь для сохранения файла отчета о прогнозировании спроса
            $basePath = $this->getParameter('kernel.project_dir')
                . '/public/uploads/demandForecast/' . $user->getUsername();

            if ($demandForecastDto->object_analysis === 'file') {
                // Создание объекта класса DemandForecastFile из Dto
                $file = $fileRepository->findOneBy(['filename' => $demandForecastDto->file]);
                $demandForecastFile = DemandForecastFile::fromDto($user, $demandForecastDto, null, $file);
            } else {
                $category = $categoryRepository->findOneBy([
                    'name' => $demandForecastDto->category,
                    'purchase_user' => $user
                    ]);
                // Запрос в сервис объединения файлов продаж

                // Получение всех файлов продаж категории
                $salesFiles = $category->getSalesFiles();

                // Формирование названия для файла
                $filename = $demandForecastDto->category . '_' . (new \DateTime())->getTimestamp() . '.csv';

                // Создание экземпляра класса для работы с .csv файлами
                $csvFile = new CsvService(
                    $pathToFile . '/' . $filename,
                    $salesFiles,
                    $pathToFile,
                    $demandForecastDto->column
                );

                // Создаем новый файл продаж
                $csvFile->aggregationSalesFiles();

                $file = new SalesFile();
                $file->setFilename($filename);
                $file->setSeparator(';');
                $file->setCategory($category);
                $file->setCreatedAt(new \DateTime());
                $file->setPurchaseUser($user);
                $file->setCreatedByCategory(true);

                // Сохраняем файл в бд
                $entityManager->persist($file);
                $demandForecastFile = DemandForecastFile::fromDto($user, $demandForecastDto, $category, $file);
            }

            // Запрос в сервис прогнозирования спроса
            try {
                $demandForecast = new DemandForecastService($pathToFile, $basePath);
                if ($demandForecastFile->getAnalysisMethodFormatNumber() === 1) {
                    $result = $demandForecast->getHoldWinterPredictionFromFile($demandForecastDto, $file);
                } else {
                    $result = $demandForecast->getARIMAPredictionFromFile($demandForecastDto, $file);
                }

                $demandForecastFile->setAccuracy($result['percentage_accuracy']);
                $demandForecastFile->setRmse($result['accuracy']);
                $demandForecastFile->setCreatedAt(new \DateTime());

                // Сохраняем отчет в бд
                $entityManager->persist($demandForecastFile);
                $entityManager->flush();

                if ($demandForecastFile) {
                    $data = [
                        'code' => Response::HTTP_OK,
                        'message' => 'Отчет был создан'
                    ];
                    $response->setStatusCode(Response::HTTP_OK);
                } else {
                    $data = [
                        'code' => Response::HTTP_CONFLICT,
                        'message' => 'Отчет не был создан'
                    ];
                    $response->setStatusCode(Response::HTTP_CONFLICT);
                }
            } catch (DemandForecastServiceException $e) {
                $data = [
                    'code' => Response::HTTP_BAD_REQUEST,
                    'message' => $e->getMessage()
                ];
                $response->setStatusCode(Response::HTTP_BAD_REQUEST);
            }
        } else {
            $data = [
                'code' => Response::HTTP_BAD_REQUEST,
                'message' => $errors
            ];
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
        }

        $response->setContent($serializer->serialize($data, 'json'));
        $response->headers->add(['Content-Type' => 'application/json']);
        return $response;
    }

    /**
     *
     * @OA\Get(
     *     path="/api/v1/demand_forecast/{id}",
     *     tags={"demand forecast"},
     *     summary="Получение отчета о прогнозировании спроса пользователя.",
     *     description="Данный метод доступен только авторизованным пользователям.",
     *     operationId="demand_forecast.show",
     *     @OA\Response(
     *          response="200",
     *          description="Успешная операция",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="filename",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="start_period_analysis",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="end_period_analysis",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="column",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="start_period_forecast",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="end_period_forecast",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="method",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="accuracy",
     *                  type="number",
     *                  format="float"
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
     * @Route("/{id}", name="demand_forecast_file_show", methods={"GET"})
     * @param DemandForecastFile $demandForecastFile
     * @param SerializerInterface $serializer
     * @return Response
     * @throws \Exception
     */
    public function show(DemandForecastFile $demandForecastFile, SerializerInterface $serializer): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $userRepository = $entityManager->getRepository(User::class);
        $demandForecastFileRepository = $entityManager->getRepository(DemandForecastFile::class);

        $response = new Response();

        // Проверяем является ли пользователь создателем данного отчета
        $user = $userRepository->findOneBy(['email' => $this->getUser()->getUsername()]);
        if ($demandForecastFile->getPurchaseUser() === $user) {
            // Получаем json файл отчета
            $file = $this->getParameter('kernel.project_dir') . "/public/uploads/demandForecast/" .
                $user->getUsername() . "/" . $demandForecastFile->getFilename() . ".json";
            $content = file_get_contents($file);
            $json = json_decode($content, true);

            // Формируем ответ
            $data = [
                'filename' => $demandForecastFile->getFilename(),
                'start_period_analysis' => (new \DateTime($json['start_period_analysis']))->format('d.m.Y'),
                'end_period_analysis' => (new \DateTime($json['end_period_analysis']))->format('d.m.Y'),
                'column' => $demandForecastFile->getAnalysisField(),
                'start_period_forecast' => (new \DateTime($json['start_period_forecast']))->format('d.m.Y'),
                'end_period_forecast' => (new \DateTime($json['end_period_forecast']))->format('d.m.Y'),
                'method' => $demandForecastFile->getAnalysisMethodFormatString(),
                'accuracy' => round($demandForecastFile->getRmse(), 2),
                'prediction' => $json['prediction'],
                'origin_data' => $json['origin_data'],
                'percentage_accuracy' => $json['percentage_accuracy']
            ];
            $response->setStatusCode(Response::HTTP_OK);
        } else {
            $data = [
                'code' => Response::HTTP_BAD_REQUEST,
                'message' => 'Вы не являетесь владельцем отчета.'
            ];
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
        }

        $response->setContent($serializer->serialize($data, 'json'));
        $response->headers->add(['Content-Type' => 'application/json']);
        return $response;
    }

    /**
     *
     * @OA\Delete(
     *     path="/api/v1/demand_forecast/{id}",
     *     tags={"demand forecast"},
     *     summary="Удаление файла отчета о прогнозировании спроса.",
     *     description="Данный метод доступен только авторизованным пользователям.",
     *     operationId="demand_forecast.delete",
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
     * @Route("/{id}", name="demand_forecast_file_delete", methods={"DELETE"})
     * @param DemandForecastFile $demandForecastFile
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function delete(DemandForecastFile $demandForecastFile, SerializerInterface $serializer): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $userRepository = $entityManager->getRepository(User::class);
        $demandForecastFileRepository = $entityManager->getRepository(DemandForecastFile::class);

        $response = new Response();
        $userJWT = $this->getUser();
        $user = $userRepository->findOneBy(['email' => $userJWT->getUsername()]);

        if ($demandForecastFileRepository->findOneBy([
            'purchase_user' => $user->getId(),
            'filename' => $demandForecastFile->getFilename()])) {
            // Удаляем файл из файловой системы
            $basePath = $this->getParameter('kernel.project_dir')
                . '/public/uploads/demandForecast/' . $userJWT->getUsername();

            if (is_file($basePath . '/' . $demandForecastFile->getFilename() . '.json')) {
                unlink($basePath . '/' . $demandForecastFile->getFilename() . '.json');
            }

            // Удаляем запись о файле из БД
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($demandForecastFile);
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
                "message" => "Вы не являетесь владельцем отчета о прогнозировании спроса."
            ];
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
        }

        $response->setContent($serializer->serialize($data, 'json'));
        $response->headers->add(['Content-Type' => 'application/json']);
        return $response;
    }
}
