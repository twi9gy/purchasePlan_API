<?php


namespace App\Model\Request;

use JMS\Serializer\Annotation as Serialization;
use Symfony\Component\Validator\Constraints as Assert;

class SalesFileDtoRequest
{
    /**
     * @Serialization\Type("integer")
     */
    public $category_id;

    /**
     * @Serialization\Type("string")
     * @Assert\NotBlank()
     * @Assert\Length (min="3")
     */
    public $filename;
}