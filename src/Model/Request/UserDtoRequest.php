<?php

namespace App\Model\Request;

use JMS\Serializer\Annotation as Serialization;
use Symfony\Component\Validator\Constraints as Assert;

class UserDtoRequest
{
    /**
     * @Serialization\Type("string")
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    public $email;

    /**
     * @Serialization\Type("string")
     * @Assert\NotBlank()
     * @Assert\Length(min=8)
     */
    public $password;

    /***
     * @Serialization\Type ("string")
     * @Assert\NotBlank()
     * @Assert\Length(min=3)
     */
    public $companyName;
}