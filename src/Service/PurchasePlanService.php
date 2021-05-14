<?php

namespace App\Service;

use App\Entity\DemandForecastFile;
use App\Exception\PlanningPurchaseServiceException;
use App\Model\PurchasePlanDto;

class PurchasePlanService
{
    private $pathToFile;
    private $pathToSave;
    private $baseUri;

    public function __construct(string $pathToFile, string $pathToSave)
    {
        $this->pathToFile = $pathToFile;
        $this->pathToSave = $pathToSave;
        $this->baseUri = $_ENV['PURCHASE_PLAN_SERVICE'];
    }

    /**
     * @param PurchasePlanDto $request
     * @param DemandForecastFile $file
     * @return array
     * @throws PlanningPurchaseServiceException
     */
    public function getPurchasePlan(PurchasePlanDto $request, DemandForecastFile $file): array
    {
        // Формируем данные для анализа
        $cFile = curl_file_create($this->pathToFile . '/' . $request->forecast_file . '.json');
        $postData = array('file'=> $cFile);

        $uri = $this->baseUri . 'purchase_plan/create' .
            '?freq_interval=' . $file->getInterval() . '&service_level=' . $request->service_level .
            '&storage_costs=' . $request->storage_cost . '&product_price=' . $request->product_price .
            '&shipping_costs=' . $request->shipping_costs . '&time_shipping=' . $request->time_shipping;

        if ($request->delayed_deliveries !== null) {
            $uri .= '&delayed_deliveries=' . $request->delayed_deliveries;
        }

        // Создаем запрос в сервис
        $ch = curl_init($uri);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $responseData = curl_exec($ch);

        // Выбрасываем ошибку. Сервис недоступен
        if ($responseData === false) {
            throw new PlanningPurchaseServiceException('Сервис временно недоступен. 
            Попробуйте создать план заукпок позднее ' . $cFile->getFilename());
        }

        curl_close($ch);

        $result = json_decode($responseData, true);
        if (isset($result['code']) && $result['code'] === 403) {
            throw new PlanningPurchaseServiceException($result['message']);
        }

        if (!is_dir($this->pathToSave)
            && !mkdir($this->pathToSave, 0777, true)
            && !is_dir($this->pathToSave)) {
            throw new \Exception(sprintf('Directory "%s" was not created', $this->pathToSave));
        }

        // Сохранение результата в ФС
        file_put_contents(
            $this->pathToSave . '/' . $request->filename . '.json',
            $responseData
        );

        return $result;
    }
}
