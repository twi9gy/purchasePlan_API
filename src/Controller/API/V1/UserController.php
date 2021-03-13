<?php

namespace App\Controller\API\V1;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use http\Message;
use JMS\Serializer\SerializerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\AuthorizationHeaderTokenExtractor;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/api/v1/user")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/current", name="userCurrent", methods={"GET"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param SerializerInterface $serializer
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request, SerializerInterface $serializer) : Response
    {
        // Получаем пользователя
        $user = $this->getUser();
        if ($user === null)
        {
            // Формируем ответ
            $data = [
                'code' => Response::HTTP_CONFLICT,
                'message' => 'User not found',
            ];
        } else {
            // Формируем ответ
            $data = [
                'code' => Response::HTTP_OK,
                'email' => $user->getUsername(),
                'roles' => $user->getRoles(),
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
     * @Route ("/changePass", name="changePass", methods={"PUT"})
     * @param Request $request
     * @param \App\Repository\UserRepository $userRepository
     * @param \Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface $passwordEncoder
     * @param \JMS\Serializer\SerializerInterface $serializer
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function changePasswod(Request $request, UserRepository $userRepository,
                                  UserPasswordEncoderInterface $passwordEncoder, SerializerInterface $serializer) : Response
    {
        // Получаем новый пароль
        $pass = $request->getContent();
        // Получаем пользователя
        $user = $this->getUser();

        if ($user === null)
        {
            // Формируем ответ
            $data = [
                'code' => Response::HTTP_CONFLICT,
                'message' => 'User not found',
            ];
        } else {
            // Устанавливаем новый пароль и сохраняем хеш
            try {
                $userRepository->upgradePassword($user, $passwordEncoder->encodePassword($user, $pass));

                // Формируем ответ
                $data = [
                    'code' => Response::HTTP_OK,
                    'message' => 'Password has been changed',
                ];
            } catch (OptimisticLockException | ORMException $e) {
                // Формируем ответ
                $data = [
                    'code' => Response::HTTP_CONFLICT,
                    'message' => $e
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
}
