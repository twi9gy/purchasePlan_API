<?php

namespace App\Controller\API\V1;

use App\Model\Request\UserDtoRequest;
use App\Repository\UserRepository;
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
     * @Route ("/signin", name="signin", methods={"POST"})
     *
     *
     * @OA\Response(
     *     response="200",
     *     description="User authorize",
     *     @OA\JsonContent(@OA\Schema (type="string"))
     * )
     *
     * @OA\Response (
     *     response="403",
     *     description="User not found",
     *     @OA\JsonContent(@OA\Schema (type="string"))
     * )
     *
     * @param Request $request
     * @return Response
     */
    public function login(Request $request): Response
    {
//      Параметры на вход JWTTokenManagerInterface $JWTManager, SerializerInterface $serializer, UserRepository $userRepository

//        // Десериализация запроса в Dto
//        $userDto = $serializer->deserialize($request->getContent(), UserDtoRequest::class, 'json');
//        // Ищем пользователя
//        $user = $userRepository->findUserByEmail($userDto->email);
          // Нет проверки пароля
//        if ($user) {
//            // Формируем ответ сервера
//            $data = [
//                "code" => Response::HTTP_OK,
//                'token' => $JWTManager->create($user)
//            ];
//        } else {
//            // Формируем ответ сервера
//            $data = [
//                "code" => Response::HTTP_FORBIDDEN,
//                'message' => 'User not found.'
//            ];
//        }
//        return new Response(
//        // Сериализуем ответ в Json
//            $serializer->serialize($data, 'json')
//        );
    }

    /**
     * @Route("/signup", name="register", methods={"POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\Validator\Validator\ValidatorInterface $validator
     * @param \JMS\Serializer\SerializerInterface $serializer
     * @param \Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface $passwordEncoder
     * @param \Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface $JWTManager
     * @param UserRepository $userRepository
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function register(Request $request, ValidatorInterface $validator,
                          SerializerInterface $serializer, UserPasswordEncoderInterface $passwordEncoder,
                          JWTTokenManagerInterface $JWTManager, UserRepository $userRepository) : Response
    {
        // Десериализация запроса в Dto
        $userDto = $serializer->deserialize($request->getContent(), UserDtoRequest::class, 'json');
        // Проверка ошибок валидации
        $errors = $validator->validate($userDto);

        if ($userRepository->findOneBy(['email' => $userDto->email])) {
            // Формируем ответ сервера
            $data = [
                "code" => Response::HTTP_CONFLICT,
                "message" => 'User already exist'
            ];
        } else {
            if (count($errors) > 0)
            {
                // Формируем ответ сервера
                $data = [
                    "code" => Response::HTTP_CONFLICT,
                    "message" => $errors
                ];
            } else {
                // Создаем пользователя из Dto
                $user = \App\Entity\User::fromDto($userDto);
                // Хешируем пароль
                $user->setPassword($passwordEncoder->encodePassword(
                    $user,
                    $user->getPassword()
                )
                );
                // Сохраняем пользователя в базе данных
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();

                // Формируем ответ сервера
                $data = [
                    "code" => Response::HTTP_CREATED,
                    'token' => $JWTManager->create($user)
                ];
            }
        }

        $response = new Response();
        $response->setContent($serializer->serialize($data, 'json'));
        if ($data['code'] === Response::HTTP_CREATED) {
            $response->setStatusCode(Response::HTTP_CREATED);
        } else {
            $response->setStatusCode(Response::HTTP_CONFLICT);
        }

        return $response;
    }
}
