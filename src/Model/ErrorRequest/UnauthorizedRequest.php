<?php

namespace App\Model\ErrorRequest;

use JMS\Serializer\Annotation as Serialization;
use OpenApi\Annotations as OA;

/**
 *
 *  @OA\Schema(
 *     title="Unauthorized Request",
 *     description="Не авторизован"
 * )
 *
 * Class UnauthorizedRequest
 * @package App\Model\ErrorRequest
 */
class UnauthorizedRequest
{
    /**
     * @OA\Property(
     *     format="intaeger",
     *     title="code",
     *     description="Код ошибки",
     *     example="401"
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
     *     example="Invalid credentials."
     * )
     * @Serialization\Type("string")
     */
    public $message;
}
