<?php

namespace App\Controller;

use App\Entity\DemandForecastFile;
use App\Entity\PurchasePlan;
use App\Entity\User;
use App\Exception\PlanningPurchaseServiceException;
use App\Model\PurchasePlanDto;
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
        $purchasePlans = $purchasePlanRepository->findBy(['purchase_user' => $user->getId()]);

        // Формируем массив файлов предсказания спроса
        $result = [];
        foreach ($purchasePlans as $file) {
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
        $response->setContent($serializer->serialize($data, 'json'));
        $response->headers->add(['Content-Type' => 'application/json']);
        return $response;
    }

    /**
     *
     *  @OA\Post(
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
     * @return Response
     */
    public function new(
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ): Response {
        $entityManager = $this->getDoctrine()->getManager();
        $userRepository = $entityManager->getRepository(User::class);
        $demandForecastRepository = $entityManager->getRepository(DemandForecastFile::class);

        $response = new Response();

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
            // Получаем текущего пользователя
            $email = $this->getUser()->getUsername();
            $user = $userRepository->findOneBy(['email' => $email]);

            // Получаение отчета о прогнозировании спроса
            $demandForecastFile = $demandForecastRepository->findOneBy(['filename' => $planDto->forecast_file]);

            // Создаем объект План закупок из Dto
            $plan = PurchasePlan::fromDto($user, $planDto, $demandForecastFile);

            // Получаем путь до файла отчета о предсказания спроса
            $pathToDemandFile = $this->getParameter('kernel.project_dir')
                . '/public/uploads/demandForecast/' . $user->getUsername();

            // Путь для сохранения файла
            $basePath = $this->getParameter('kernel.project_dir')
                . '/public/uploads/Plans/' . $user->getUsername();

            // Запрос в сервис создания планов закупок
            try {
                $purchasePlanService = new PurchasePlanService($pathToDemandFile, $basePath);
                $purchasePlan = $purchasePlanService->getPurchasePlan($planDto);
                $purchasePlan = json_decode($purchasePlan, true);

                $plan->setFreqDelivery($purchasePlan['freq_delivery']);
                $plan->setOrderPoint($purchasePlan['point_order']);
                $plan->setReserve($purchasePlan['reserve']);
                $plan->setSizeOrder($purchasePlan['size_order']);
                $plan->setTotalCost($purchasePlan['total_costs']);

                // Сохраняем отчет в бд
                $entityManager->persist($plan);
                $entityManager->flush();

                $data = [
                    'code' => Response::HTTP_CREATED,
                    'message' => 'План закупок был создан'
                ];
                $response->setStatusCode(Response::HTTP_CREATED);
            } catch (PlanningPurchaseServiceException $e) {
                $data = [
                    'code' => Response::HTTP_BAD_REQUEST,
                    'message' => $e->getMessage()
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
