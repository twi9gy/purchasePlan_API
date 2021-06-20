<?php


namespace App\Model;

use JMS\Serializer\Annotation as Serialization;
use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Annotations as OA;

/**
 *
 * @OA\Schema(
 *     title="PurchasePlanDto",
 *     description="Purchase Plan Dto"
 * )
 *
 * Class PurchasePlanDto
 * @package App\Model
 */
class PurchasePlanDto
{
    /**
     *
     * @OA\Property(
     *     format="string",
     *     title="filename",
     *     description="Название плана закупок"
     * )
     *
     * @Serialization\Type("string")
     * @Assert\NotBlank()
     * @Assert\Length (min="3")
     */
    public $filename;

    /**
     *
     * @OA\Property(
     *     format="integer",
     *     title="service_level",
     *     description="Уровень обслуживания."
     * )
     *
     * @Serialization\Type("integer")
     * @Assert\NotBlank()
     */
    public $service_level;

    /**
     *
     * @OA\Property(
     *     format="string",
     *     title="forecast_file",
     *     description="Файл спрогнозированного спроса."
     * )
     *
     * @Serialization\Type("string")
     * @Assert\NotBlank()
     */
    public $forecast_file;

    /**
     *
     * @OA\Property(
     *     format="float",
     *     title="shipping_costs",
     *     description="Затрыты на достувку продукции."
     * )
     *
     * @Serialization\Type("float")
     * @Assert\NotBlank()
     */
    public $shipping_costs;

    /**
     *
     * @OA\Property(
     *     format="float",
     *     title="storage_cost",
     *     description="Затраты на хранение продукции."
     * )
     *
     * @Serialization\Type("float")
     * @Assert\NotBlank()
     */
    public $storage_cost;

    /**
     *
     * @OA\Property(
     *     format="integer",
     *     title="time_shipping",
     *     description="Колличество дней доставки продукции."
     * )
     *
     * @Serialization\Type("integer")
     * @Assert\NotBlank()
     */
    public $time_shipping;

    /**
     *
     * @OA\Property(
     *     format="float",
     *     title="product_price",
     *     description="Стоимость единицы продукции."
     * )
     *
     * @Serialization\Type("float")
     * @Assert\NotBlank()
     */
    public $product_price;

    /**
     *  @OA\Property(
     *     format="integer",
     *     title="delayed_deliveries",
     *     description="Задержка поставок."
     * )
     * @Serialization\Type("integer")
     */
    public $delayed_deliveries;

    /**
     *  @OA\Property(
     *     format="integer",
     *     title="production_quantity",
     *     description="Текущий уровень запасов."
     * )
     * @Serialization\Type("integer")
     */
    public $production_quantity;
}
