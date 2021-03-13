<?php


namespace App\Model\Request;

use JMS\Serializer\Annotation as Serialization;
use Symfony\Component\Validator\Constraints as Assert;

class CategoryDtoRequest
{
    /**
     * @Serialization\Type("string")
     * @Assert\NotBlank()
     * @Assert\Length (min="3")
     */
    public $name;

    /**
     * @Serialization\Type("integer")
     */
    public $parent_id;
}