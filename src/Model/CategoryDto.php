<?php


namespace App\Model;

use JMS\Serializer\Annotation as Serialization;
use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Annotations as OA;

/**
 *
 * @OA\Schema(
 *     title="CategoryDto",
 *     description="Category Dto"
 * )
 *
 * Class CategoryDto
 * @package App\Model
 */
class CategoryDto
{
    /**
     *
     * @OA\Property(
     *     format="string",
     *     title="name",
     *     description="Название категории",
     *     example="Ручки"
     * )
     *
     * @Serialization\Type("string")
     * @Assert\NotBlank()
     * @Assert\Length (min="3")
     */
    public $name;

    /**
     *
     * @OA\Property(
     *     format="integer",
     *     title="parent_id",
     *     description="Номер родительской категории",
     *     example="null"
     * )
     *
     * @Serialization\Type("integer")
     */
    public $parent_id;
}
