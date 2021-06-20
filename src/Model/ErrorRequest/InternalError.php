<?php

namespace App\Model\ErrorRequest;

use JMS\Serializer\Annotation as Serialization;
use OpenApi\Annotations as OA;

/**
 *
 *  @OA\Schema(
 *     title="Internal Error Request",
 *     description="Внутренняя ошибка сервера"
 * )
 *
 * Class InternalError
 * @package App\Model\ErrorRequest
 */
class InternalError
{
    /**
     * @OA\Property(
     *     format="intaeger",
     *     title="code",
     *     description="Код ошибки",
     *     example="500"
     * )
     *
     * @Serialization\Type("integer")
     */
    public $code;

    /**
     * @OA\Property(
     *     format="string",
     *     title="message",
     *     description="Сообщение ошибки",
     *     example="Сообщение ошибки"
     * )
     * @Serialization\Type("string")
     */
    public $message;
}
