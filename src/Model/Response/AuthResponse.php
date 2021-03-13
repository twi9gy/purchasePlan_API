<?php


namespace App\Model\Response;

use OpenApi\Annotations as OA;

/**
 *@OA\Schema (
 *     description="Authorize response",
 *     title="Authorize response",
 * )
 */
class AuthResponse
{
    /***
     * @OA\Property (
     *     description="Code",
     *     title="Code",
     *     format="int32"
     * )
     * @var int
     */
    public $code;

    /***
     * @OA\Property (
     *     description="Message",
     *     title="Message"
     * )
     * @var string
     */
    public $message;

    /***
     * @OA\Property (
     *     description="JWT Token",
     *     title="JWT Token"
     * )
     * @var string
     */
    public $token;
}