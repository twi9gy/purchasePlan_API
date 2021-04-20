<?php

namespace App\Model;

use JMS\Serializer\Annotation as Serialization;
use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Annotations as OA;

/**
 *
 *  @OA\Schema(
 *     title="UserDto",
 *     description="User Dto"
 * )
 *
 * Class UserDto
 * @package App\Model\Request
 */
class UserDto
{
    /**
     *
     * @OA\Property(
     *     format="email",
     *     title="Email",
     *     description="Email",
     *     example="testUser@gmail.com"
     * )
     *
     * @Serialization\Type("string")
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    public $email;

    /**
     *
     *  @OA\Property(
     *     format="string",
     *     title="Password",
     *     description="Password",
     *     example="12345678"
     * )
     *
     * @Serialization\Type("string")
     * @Assert\NotBlank()
     * @Assert\Length(min=8)
     */
    public $password;

    /**
     *
     *  @OA\Property(
     *     format="string",
     *     title="companyName",
     *     description="Company name",
     *     example="Microsoft"
     * )
     *
     * @Serialization\Type ("string")
     * @Assert\NotBlank()
     * @Assert\Length(min=3)
     */
    public $company_name;
}
