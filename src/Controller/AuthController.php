<?php

namespace App\Controller;

use App\Entity\User;
use App\Model\UserDto;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use Gesdinet\JWTRefreshTokenBundle\Service\RefreshToken;
use JMS\Serializer\SerializerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/api/v1/auth")
 */
class AuthController extends AbstractController
{
    /**
     *
     * @OA\Post(
     *     path="/api/v1/auth/signin",
     *     tags={"user"},
     *     summary="Авторизация пользователя.",
     *     description="При верности логина и пароля возвращает JWT - токен для работы с системой.",
     *     operationId="signin",
     *     @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="username",
     *                  type="string",
     *                  example="vadim@mail.ru"
     *              ),
     *              @OA\Property(
     *                  property="password",
     *                  type="string",
     *                  example="vadim123"
     *              )
     *          )
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="Успешная операция.",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="token",
     *                  type="string"
     *              )
     *          )
     *     ),
     *     @OA\Response(
     *          response="401",
     *          description="Invalid credentials",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="code",
     *                  type="string",
     *                  example="401"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  example="Invalid credentials."
     *              )
     *          )
     *     )
     * )
     *
     * @Route ("/signin", name="signin", methods={"POST"})
     * @param Request $request
     * @return Response
     */
    public function login(Request $request): Response
    {
    }

    /**
     *
     * @OA\Post(
     *     path="/api/v1/auth/signup",
     *     tags={"user"},
     *     summary="Регистрация пользователя",
     *     operationId="signup",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UserDto")
     *     ),
     *     @OA\Response(
     *          response="201",
     *          description="Успешная операция",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="token",
     *                  type="string"
     *              )
     *          )
     *     ),
     *     @OA\Response(
     *          response="500",
     *          description="Сервер недоступен"
     *     ),
     *     @OA\Response(
     *          response="400",
     *          description="Ошибка валидации",
     *          @OA\JsonContent(ref="#/components/schemas/FailResponse")
     *     ),
     *     @OA\Response(
     *          response="409",
     *          description="Пользователь уже существует",
     *          @OA\JsonContent(ref="#/components/schemas/FailResponse")
     *     )
     * )
     *
     * @Route("/signup", name="register", methods={"POST"})
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validator
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param JWTTokenManagerInterface $JWTManager
     * @return Response
     */
    public function register(
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        UserPasswordEncoderInterface $passwordEncoder,
        JWTTokenManagerInterface $JWTManager,
        RefreshTokenManagerInterface $refreshTokenManager
    ) : Response {
        // Десериализация запроса в Dto
        $userDto = $serializer->deserialize($request->getContent(), UserDto::class, 'json');
        // Проверка ошибок валидации
        $errors = $validator->validate($userDto);

        $entityManager = $this->getDoctrine()->getManager();
        $userRepository = $entityManager->getRepository(User::class);

        $response = new Response();

        if ($userRepository->findOneBy(['email' => $userDto->email])) {
            // Формируем ответ сервера
            $data = [
                "code" => Response::HTTP_CONFLICT,
                "message" => 'Пользователь уже существует'
            ];
            $response->setStatusCode(Response::HTTP_CONFLICT);
        } elseif (count($errors) > 0) {
            // Формируем ответ сервера
            $data = [
                "code" => Response::HTTP_BAD_REQUEST,
                "message" => $errors
            ];
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
        } else {
            // Создаем пользователя из Dto
            $user = User::fromDto($userDto);
            // Хешируем пароль
            $user->setPassword($passwordEncoder->encodePassword(
                $user,
                $user->getPassword()
            ));
            // Сохраняем пользователя в базе данных
            $entityManager->persist($user);
            $entityManager->flush();

            // Устанавливаем токен обновления
            $refreshToken = $refreshTokenManager->create();
            $refreshToken->setUsername($user->getEmail());
            $refreshToken->setRefreshToken();
            $refreshToken->setValid((new \DateTime())->modify('+1 month'));
            $refreshTokenManager->save($refreshToken);

            // Формируем ответ сервера
            $data = [
                'code' => Response::HTTP_CREATED,
                'token' => $JWTManager->create($user),
                'refresh_token' => $refreshToken->getRefreshToken()
            ];
            $response->setStatusCode(Response::HTTP_CREATED);
        }

        $response->setContent($serializer->serialize($data, 'json'));
        $response->headers->add(['Content-Type' => 'application/json']);
        return $response;
    }

    /**
     *
     * @OA\Post(
     *     path="/api/v1/auth/token/refresh",
     *     tags={"user"},
     *     summary="Refresh tonek",
     *     operationId="token.refresh",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *              @OA\Property(
     *                  property="refresh_token",
     *                  type="string"
     *              )
     *         )
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="Operation successful",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="token",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="refresh_token",
     *                  type="string"
     *              )
     *          )
     *     ),
     *     @OA\Response(
     *          response="401",
     *          description="Unauthorized",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="code",
     *                  type="integer",
     *                  example="401"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  example="An authentication exception occurred."
     *              )
     *          )
     *     )
     * )
     *
     * @Route("/token/refresh", name="refresh", methods={"POST"})
     * @param Request $request
     * @param RefreshToken $refreshService
     * @return mixed
     */
    public function refresh(Request $request, RefreshToken $refreshService)
    {
        return $refreshService->refresh($request);
    }
}
