<?php

namespace App\Model\ErrorRequest;

use JMS\Serializer\Annotation as Serialization;
use OpenApi\Annotations as OA;

/**
 *
 *  @OA\Schema(
 *     title="Conflict Request",
 *     description="Конфликт"
 * )
 *
 * Class ConflictRequest
 * @package App\Model\ErrorRequest
 */
class ConflictRequest
{
    /**
     * @OA\Property(
     *     format="intaeger",
     *     title="code",
     *     description="Код ошибки",
     *     example="409"
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
