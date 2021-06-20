<?php


namespace App\Model;

use JMS\Serializer\Annotation as Serialization;
use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Annotations as OA;

/**
 *
 * @OA\Schema(
 *     title="DemandForecastFileDto",
 *     description="Demand Forecast File Dto"
 * )
 *
 * Class DemandForecastFileDto
 * @package App\Model
 */
class DemandForecastFileDto
{
    /**
     *
     * @OA\Property(
     *     format="string",
     *     title="filename",
     *     description="Название отчета"
     * )
     *
     * @Serialization\Type("string")
     * @Assert\NotBlank()
     * @Assert\Length (min="3")
     */
    public $filename;

    /**
     *
     *  @OA\Property(
     *     format="string",
     *     title="method",
     *     description="Название метода для прогнозирования спроса"
     * )
     *
     * @Serialization\Type("string")
     * @Assert\NotBlank()
     */
    public $method;

    /**
     *
     *  @OA\Property(
     *     format="string",
     *     title="object_analysis",
     *     description="Тип анализируемого объекта (файл, категория)."
     * )
     *
     * @Serialization\Type("string")
     * @Assert\NotBlank()
     */
    public $object_analysis;

    /**
     *
     * @OA\Property(
     *     format="string",
     *     title="catogory",
     *     description="Категория, по которой проводился анализ временного ряда."
     * )
     *
     * @Serialization\Type("string")
     */
    public $category;

    /**
     *
     * @OA\Property(
     *     format="string",
     *     title="file",
     *     description="Файл, по которому проводился анализ временного ряда."
     * )
     *
     * @Serialization\Type("string")
     */
    public $file;


    /**
     *
     * @OA\Property(
     *     format="string",
     *     title="freq",
     *     description="Интервал анализа временного ряда (день, неделя, месяц)."
     * )
     *
     * @Serialization\Type("string")
     */
    public $freq;

    /**
     *
     * @OA\Property(
     *     format="string",
     *     title="freq",
     *     description="Название столбца в файле продаж. По этому столбцу будет проведен анализ временного ряда."
     * )
     *
     * @Serialization\Type("string")
     */
    public $column;

    /**
     *
     * @OA\Property(
     *     format="integer",
     *     title="period",
     *     description="Период прогнозирования спроса"
     * )
     *
     * @Serialization\Type("integer")
     */
    public $period;

    /**
     *
     * @OA\Property(
     *     format="integer",
     *     title="seasonal",
     *     description="Периодичность данных"
     * )
     *
     * @Serialization\Type("integer")
     */
    public $seasonal;
}
