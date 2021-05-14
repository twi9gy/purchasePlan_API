<?php

namespace App\Controller;

use App\Entity\PurchasePlan;
use App\Entity\User;
use App\Exception\PlanningPurchaseServiceException;
use App\Model\PurchasePlanDto;
use App\Repository\DemandForecastFileRepository;
use App\Repository\UserRepository;
use App\Service\PurchasePlanService;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use OpenApi\Annotations as OA;

/**
 * @Route("/api/v1/purchase_plan")
 */
class PurchasePlanController extends AbstractController
{
    /**
     *
     * @OA\Get(
     *     path="/api/v1/purchase_plan/",
     *     tags={"purchase plan"},
     *     summary="Получение всех планов закупок пользователя.",
     *     description="Данный метод доступен только авторизованным пользователям.",
     *     operationId="purchase_plan.index",
     *     @OA\Response(
     *          response="200",
     *          description="Успешная операция",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="plans",
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
     * @Route("/", name="purchase_plan_index", methods={"GET"})
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function index(SerializerInterface $serializer): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $userRepository = $entityManager->getRepository(User::class);
        $purchasePlanRepository = $entityManager->getRepository(PurchasePlan::class);

        $response = new Response();

        // Получаем пользователя
        $user = $userRepository->findOneBy(['email' => $this->getUser()->getUsername()]);

        // Получаем планы закупок пользователя
        $purchasePlans = $purchasePlanRepository->findByUser($user->getId());

        // Формируем ответ сервера
        $data = [
            "code" => Response::HTTP_OK,
            "files" => $purchasePlans
        ];

        $response->setStatusCode(Response::HTTP_OK);
        $response->setContent($serializer->serialize($data, 'json'));
        $response->headers->add(['Content-Type' => 'application/json']);
        return $response;
    }

    /**
     *
     * @OA\Post(
     *     path="/api/v1/purchase_plan/new",
     *     tags={"purchase plan"},
     *     summary="Создание нового плана закупок.",
     *     description="Данный метод доступен только авторизованным пользователям.",
     *     operationId="purchase_plan.new",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/PurcahsePlanDto")
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
     * @Route("/new", name="purchase_plan_new", methods={"POST"})
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validator
     * @param UserRepository $userRepository
     * @param DemandForecastFileRepository $demandForecastFileRepository
     * @return Response
     */
    public function new(
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        UserRepository $userRepository,
        DemandForecastFileRepository $demandForecastFileRepository
    ): Response {
        $entityManager = $this->getDoctrine()->getManager();

        // Десериализация запроса в Dto
        $planDto = $serializer->deserialize(
            $request->getContent(),
            PurchasePlanDto::class,
            'json'
        );
        // Проверка ошибок валидации
        $errors = $validator->validate($planDto);

        $response = new Response();

        if (count($errors) === 0) {
            try {
                // Получаем текущего пользователя
                $user = $userRepository->findOneBy(['email' => $this->getUser()->getUsername()]);

                // Получаение отчета о прогнозировании спроса
                $demandForecastFile = $demandForecastFileRepository->findOneByFilename($planDto->forecast_file);

                // Создаем объект План закупок из Dto
                $plan = PurchasePlan::fromDto($user, $planDto, $demandForecastFile);

                // Получаем путь до файла отчета о предсказания спроса
                $pathToDemandFile = $this->getParameter('kernel.project_dir')
                    . '/public/uploads/demandForecast/' . $user->getUsername();

                // Путь для сохранения файла
                $basePath = $this->getParameter('kernel.project_dir')
                    . '/public/uploads/plans/' . $user->getUsername();

                // Запрос в сервис создания планов закупок
                try {
                    $purchasePlanService = new PurchasePlanService($pathToDemandFile, $basePath);
                    $purchasePlan = $purchasePlanService->getPurchasePlan($planDto, $demandForecastFile);

                    $plan->setFreqDelivery($purchasePlan['freq_delivery']);
                    $plan->setOrderPoint($purchasePlan['point_order']);
                    $plan->setReserve($purchasePlan['reserve']);
                    $plan->setSizeOrder($purchasePlan['size_order']);
                    $plan->setTotalCost($purchasePlan['total_costs']);
                    $plan->setCreatedAt(new \DateTime());

                    // Сохраняем отчет в бд
                    $entityManager->persist($plan);
                    $entityManager->flush();

                    $data = [
                        'code' => Response::HTTP_CREATED,
                        'message' => 'План закупок был создан '
                    ];
                    $response->setStatusCode(Response::HTTP_CREATED);
                } catch (PlanningPurchaseServiceException $e) {
                    $data = [
                        'code' => Response::HTTP_BAD_REQUEST,
                        'message' => $e->getMessage()
                    ];
                    $response->setStatusCode(Response::HTTP_BAD_REQUEST);
                }
            } catch (\Exception $e) {
                $data = [
                    'code' => Response::HTTP_BAD_REQUEST,
                    'message' => $e->getMessage(),
                ];
                $response->setStatusCode(Response::HTTP_BAD_REQUEST);
            }
        } else {
            $data = [
                'code' => Response::HTTP_BAD_REQUEST,
                'message' => $errors,
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
     *     path="/api/v1/purchase_plan/{id}",
     *     tags={"purchase plan"},
     *     summary="Получение планаза какупок пользователя.",
     *     description="Данный метод доступен только авторизованным пользователям.",
     *     operationId="purchase_plan.show",
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
     * @Route("/{id}", name="purchase_plan_show", methods={"GET"})
     * @param 
     * @param SerializerInterface $serializer
     * @return Response
     * @throws \Exception
     */
    public function show(PurchasePlan $plan, SerializerInterface $serializer): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $userRepository = $entityManager->getRepository(User::class);
        $purchasePlanRepository = $entityManager->getRepository(PurchasePlan::class);

        $response = new Response();

        // Проверяем является ли пользователь создателем данного плана
        $user = $userRepository->findOneBy(['email' => $this->getUser()->getUsername()]);
        if ($plan->getPurchaseUser() === $user) {
            // Получаем json файл плана закупок
            $planFile = $this->getParameter('kernel.project_dir') . "/public/uploads/plans/" .
                $user->getUsername() . "/" . $plan->getFilename() . ".json";
            $planContent = file_get_contents($planFile);
            $planJson = json_decode($planContent, true);

            // Получаем файл отчета о прогнозировании спроса, на основе которого сделан план закупок
            // Получаем json файл отчета
            $demandfile = $this->getParameter('kernel.project_dir') . "/public/uploads/demandForecast/" .
                $user->getUsername() . "/" . $plan->getDemandForecastFile()->getFilename() . ".json";
            $demandFileContent = file_get_contents($demandfile);
            $demandFileJson = json_decode($demandFileContent, true);

            // Формируем ответ
            $data = [
                'filename' => $plan->getFilename(),
                'start_date' => (new \DateTime($planJson['start_date']))->format('d.m.Y'),
                'end_date' => (new \DateTime($planJson['end_date']))->format('d.m.Y'),
                'freq_delivery' => $plan->getFreqDelivery(),
                'size_order' => $plan->getSizeOrder(),
                'point_order' => $plan->getOrderPoint(),
                'reserve' => $plan->getReserve(),
                'count_orders' => $planJson['count_orders'],
                'total_costs' => $plan->getTotalCost(),
                'orders' => $planJson['orders'],
                'demand' => $demandFileJson['prediction'],
                'product_count' => $planJson['product_count'],
                'orders_origin' => $planJson['orders_origin']
            ];

            $response->setStatusCode(Response::HTTP_OK);
        } else {
            $data = [
                'code' => Response::HTTP_BAD_REQUEST,
                'message' => 'Вы не являетесь владельцем плана закупок.'
            ];
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
        }

        $response->setContent($serializer->serialize($data, 'json'));
        $response->headers->add(['Content-Type' => 'application/json']);
        return $response;
    }

    /**
     *
     *  @OA\Delete(
     *     path="/api/v1/purchase_plan/{id}",
     *     tags={"purchase plan"},
     *     summary="Удаление плана закупок.",
     *     description="Данный метод доступен только авторизованным пользователям.",
     *     operationId="purchase_plan.delete",
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
     * @Route("/{id}", name="purchase_plan_delete", methods={"DELETE"})
     * @param PurchasePlan $plan
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function delete(PurchasePlan $plan, SerializerInterface $serializer): Response
    {
        $response = new Response();
        $userJWT = $this->getUser();

        // Удаляем файл из файловой системы
        $basePath = $this->getParameter('kernel.project_dir')
            . '/public/uploads/Plans/' . $userJWT->getUsername();

        if (is_file($basePath . '/' . $plan->getFilename() . '.json')) {
            unlink($basePath . '/' . $plan->getFilename() . '.json');
        }

        // Удаляем запись о файле из БД
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($plan);
        $entityManager->flush();

        // Формируем ответ сервера
        $data = [
            "code" => Response::HTTP_OK,
            "message" => "Файл был удален."
        ];

        $response->setStatusCode(Response::HTTP_OK);
        $response->setContent($serializer->serialize($data, 'json'));
        $response->headers->add(['Content-Type' => 'application/json']);
        return $response;
    }
}
