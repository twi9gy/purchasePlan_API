<?php


namespace App\Model;

use JMS\Serializer\Annotation as Serialization;
use OpenApi\Annotations as OA;

/**
 *
 *  @OA\Schema(
 *     title="FailResponse",
 *     description="Ответ сервера при ошибке"
 * )
 *
 * Class FailResponse
 * @package App\Model
 */
class FailResponse
{
    /**
     * @OA\Property(
     *     format="integer",
     *     title="code",
     *     description="Код ошибки"
     * )
     *
     * @Serialization\Type("integer")
     */
    public $code;

    /**
     * @OA\Property(
     *     format="string",
     *     title="message",
     *     description="Сообщение ошибки"
     * )
     * @Serialization\Type("string")
     */
    public $message;
}
