<?php

namespace App\Controller\API\V1;

use App\Entity\SalesFile;
use App\Form\SalesFileType;
use App\Model\Request\SalesFileDtoRequest;
use App\Repository\CategoryRepository;
use App\Repository\SalesFileRepository;
use App\Repository\UserRepository;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/api/v1/file/sales")
 */
class SalesFileController extends AbstractController
{
    /**
     * @Route("/", name="sales_file_index", methods={"GET"})
     * @param Request $request
     * @param \App\Repository\SalesFileRepository $salesFileRepository
     * @param \App\Repository\CategoryRepository $categoryRepository
     * @param \JMS\Serializer\SerializerInterface $serializer
     * @param \App\Repository\UserRepository $userRepository
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request, SalesFileRepository $salesFileRepository,
                          CategoryRepository $categoryRepository, SerializerInterface $serializer,
                          UserRepository $userRepository): Response
    {
        // Получаем Id категории
        $category_id = $request->get('category_id');

        // Получаем текущего пользователя
        $userToken = $this->getUser();
        $user = $userRepository->findOneBy(['email' => $userToken->getUsername()]);

        if ($user) {
            // Получаем категорию
            $category = $categoryRepository->find($category_id);
            if ($category) {
                // Узнаем является ли текущий пользователь владельцем категории
                if ($category->getUserId() == $user->getUsername()) {
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
                } else {
                    // Формируем ответ сервера
                    $data = [
                        "code" => Response::HTTP_CONFLICT,
                        "message" => 'Вы не имеете доступ к этим файлам'
                    ];
                }
            } else {
                // Формируем ответ сервера
                $data = [
                    "code" => Response::HTTP_CONFLICT,
                    "message" => 'Каталог не найден'
                ];
            }
        } else {
            // Формируем ответ сервера
            $data = [
                "code" => Response::HTTP_CONFLICT,
                "message" => 'Пользователь не авторизован'
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
     * @Route("/new", name="sales_file_new", methods={"POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \JMS\Serializer\SerializerInterface $serializer
     * @param \Symfony\Component\Validator\Validator\ValidatorInterface $validator
     * @param \App\Repository\CategoryRepository $categoryRepository
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function new(Request $request, SerializerInterface $serializer,
                        ValidatorInterface $validator, CategoryRepository $categoryRepository): Response
    {
        $fileDto = new SalesFileDtoRequest();
        $fileDto->filename = $_POST['filename'];
        $fileDto->category_id = $_POST['category_id'];
        // Проверка ошибок валидации
        $errors = $validator->validate($fileDto);

        if (count($errors) > 0) {
            // Формируем ответ сервера
            $data = [
                "code" => Response::HTTP_CONFLICT,
                "message" => $errors
            ];
        } else {
            $uploadResult = $this->uploadFile($fileDto->filename);

            if ($uploadResult['code'] === Response::HTTP_OK) {
                // Создание объекта класса Category из Dto
                $file = \App\Entity\SalesFile::fromDto($fileDto, $categoryRepository);
                $file->setCreatedAt(new \DateTime());
                $file->setEditAt($file->getCreatedAt());

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
            } else {
                $data = $uploadResult;
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
     * @Route("/{id}", name="sales_file_show", methods={"GET"})
     * @param \App\Entity\SalesFile $salesFile
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show(SalesFile $salesFile): Response
    {
        return $this->render('sales_file/show.html.twig', [
            'sales_file' => $salesFile,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="sales_file_edit", methods={"POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\SalesFile $salesFile
     * @param \JMS\Serializer\SerializerInterface $serializer
     * @param \Symfony\Component\Validator\Validator\ValidatorInterface $validator
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function edit(Request $request, SalesFile $salesFile,
                         SerializerInterface $serializer, ValidatorInterface $validator): Response
    {
        // Десериализация запроса в Dto
        $fileDto = $serializer->deserialize($request->getContent(), SalesFileDtoRequest::class, 'json');
        // Проверка ошибок валидации
        $errors = $validator->validate($fileDto);

        if (count($errors) > 0) {
            // Формируем ответ сервера
            $data = [
                "code" => Response::HTTP_CONFLICT,
                "message" => $errors
            ];
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
     * @Route("/{id}", name="sales_file_delete", methods={"DELETE"})
     * @param \App\Entity\SalesFile $salesFile
     * @param \JMS\Serializer\SerializerInterface $serializer
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete(SalesFile $salesFile, SerializerInterface $serializer): Response
    {
        // Удаляем файл из файловой системы
        // Путь до директории для загрузки
        $basePath = $this->getParameter('kernel.project_dir')
            . '/public/uploads/userFiles/';
        unlink($basePath . $salesFile->getFilename());

        // Удаляем запись о файле из БД
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($salesFile);
        $entityManager->flush();

        // Формируем ответ сервера
        $data = [
            "code" => Response::HTTP_OK,
            "message" => "Файл был удален."
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

    /***
     * @param string $filename
     * @return array
     */
    public function uploadFile(string $filename) : array
    {
        $user = $this->getUser();

        if ($user) {
            if(isset($_FILES['file'])) {
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
                        . '/public/uploads/userFiles/';

                    if (move_uploaded_file($file['tmp_name'], $basePath . $filename)) {
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
