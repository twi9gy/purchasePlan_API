<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use OpenApi\Annotations as OA;

/**
 * @Route("/api/v1/user")
 */
class UserController extends AbstractController
{
    /**
     *
     * @OA\Get(
     *     path="/api/v1/user/current",
     *     tags={"user"},
     *     summary="Получение информации о пользователе.",
     *     description="Данный метод доступен только авторизованным пользователям.",
     *     operationId="user.current",
     *     @OA\Response(
     *          response="200",
     *          description="Успешная операция",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="code",
     *                  type="string",
     *                  example="200"
     *              ),
     *              @OA\Property(
     *                  property="username",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="roles",
     *                  type="array",
     *                  @OA\Items(
     *                      type="string"
     *                  )
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
     * @Route("/current", name="userCurrent", methods={"GET"})
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function current(SerializerInterface $serializer) : Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $userRepository = $entityManager->getRepository(User::class);

        $response = new Response();
        // Получаем пользователя
        $userJwt = $this->getUser();

        if (!$userJwt) {
            // Формируем ответ
            $data = [
                'code' => Response::HTTP_BAD_REQUEST,
                'message' => 'Пользователь не найден',
            ];
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
        } else {
            $user = $userRepository->findOneBy(['email' => $userJwt->getUsername()]);
            // Формируем ответ
            $data = [
                'code' => Response::HTTP_OK,
                'email' => $user->getEmail(),
                'roles' => $user->getRoles(),
                'company_name' => $user->getCompanyName()
            ];
            $response->setStatusCode(Response::HTTP_OK);
        }

        $response->setContent($serializer->serialize($data, 'json'));
        $response->headers->add(['Content-Type' => 'application/json']);
        return $response;
    }

    /**
     *
     * @OA\Put(
     *     path="/api/v1/user/changePass",
     *     tags={"user"},
     *     summary="Изменение пароля пользователя.",
     *     description="Данный метод доступен только авторизованным пользователям.",
     *     operationId="user.changePass",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *              @OA\Property(
     *                  property="password",
     *                  type="string"
     *              ),
     *         )
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="Успешная операция",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="code",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              ),
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
     *     ),
     *     @OA\Response(
     *          response="409",
     *          description="Внутренняя ошибка",
     *          @OA\JsonContent(ref="#/components/schemas/FailResponse")
     *     ),
     *     @OA\Response(
     *          response="500",
     *          description="Сервер недоступен"
     *     ),
     * )
     *
     * @Route ("/changePass", name="changePass", methods={"PUT"})
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param UserRepository $userRepository
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return Response
     */
    public function changePassword(
        Request $request,
        SerializerInterface $serializer,
        UserRepository $userRepository,
        UserPasswordEncoderInterface $passwordEncoder
    ) : Response {
        $response = new Response();
        // Получаем новый пароль
        $data = json_decode($request->getContent(), true);
        // Получаем пользователя
        $user = $this->getUser();

        if (!$user) {
            // Формируем ответ
            $data = [
                'code' => Response::HTTP_BAD_REQUEST,
                'message' => 'Пользователь не найден',
            ];
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
        } else {
            // Устанавливаем новый пароль и сохраняем хеш
            try {
                $userRepository->upgradePassword($user, $passwordEncoder->encodePassword($user, $data['password']));

                // Формируем ответ
                $data = [
                    'code' => Response::HTTP_OK,
                    'message' => 'Пароль был изменен',
                ];
                $response->setStatusCode(Response::HTTP_OK);
            } catch (OptimisticLockException | ORMException $e) {
                // Формируем ответ
                $data = [
                    'code' => Response::HTTP_CONFLICT,
                    'message' => $e
                ];
                $response->setStatusCode(Response::HTTP_CONFLICT);
            }
        }

        $response->setContent($serializer->serialize($data, 'json'));
        $response->headers->add(['Content-Type' => 'application/json']);
        return $response;
    }
}
