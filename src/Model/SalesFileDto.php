<?php


namespace App\Model;

use JMS\Serializer\Annotation as Serialization;
use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Annotations as OA;

/**
 *
 * @OA\Schema(
 *     title="SalesFileDto",
 *     description="Sales File Dto"
 * )
 *
 * Class SalesFileDto
 * @package App\Model
 */
class SalesFileDto
{
    /**
     *
     * @OA\Property(
     *     format="integer",
     *     title="Category id",
     *     description="Идентификатор категории"
     * )
     *
     * @Serialization\Type("integer")
     */
    public $category_id;

    /**
     *
     * @OA\Property(
     *     format="string",
     *     title="Filename",
     *     description="Название файла"
     * )
     *
     * @Serialization\Type("string")
     * @Assert\NotBlank()
     * @Assert\Length (min="3")
     */
    public $filename;
}
