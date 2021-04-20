<?php


namespace App\Service;

use App\Exception\DemandForecastServiceException;
use App\Model\DemandForecastFileDto;

class DemandForecastService
{
    private $pathToFile;
    private $pathToSave;
    private $first_position;
    private $uri;
    private $baseUri;

    public function __construct(string $pathToFile, string $pathToSave)
    {
        $this->pathToFile = $pathToFile;
        $this->pathToSave = $pathToSave;
        $this->first_position = true;
        $this->baseUri = $_ENV['DEMAND_FORECAST_SERVICE'];
    }

    /**
     * @throws DemandForecastServiceException
     */
    public function getHoldWinterPredictionFromFile(DemandForecastFileDto $request): void
    {
        // Формируем данные для анализа
        $cFile = curl_file_create($this->pathToFile . '/' . $request->file);
        $postData = array('file'=> $cFile);

        // Формируем запрос в сервис
        $this->uri = $this->baseUri . 'hold_winter/prediction';
        if ($request->freq !== "") {
            $this->addProperty("freq", $request->freq);
        }
        if ($request->column !== "") {
            $this->addProperty("column", $request->column);
        }
        if ($request->delimiter !== "") {
            $this->addProperty("delimiter", $request->delimiter);
        }
        if ($request->period !== "") {
            $this->addProperty("period", $request->period);
        }

        // Создаем запрос в сервис
        $ch = curl_init($this->uri);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $responseData = curl_exec($ch);

        // Выбрасываем ошибку. Сервис недоступен
        if ($responseData === false) {
            throw new DemandForecastServiceException('Сервис временно недоступен. 
            Попробуйте создать отчет о прогнозировании спроса позже.');
        }

        curl_close($ch);

        $result = json_decode($responseData, true);
        if (isset($result['code']) && $result['code'] === 403) {
            throw new DemandForecastServiceException($result['message']);
        }

        if (!is_dir($this->pathToSave)
            && !mkdir($this->pathToSave, 0777, true)
            && !is_dir($this->pathToSave)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $this->pathToSave));
        }

        // Сохранение результата в ФС
        file_put_contents(
            $this->pathToSave . '/' . $request->filename . '.json',
            $responseData
        );
    }

    /**
     * @throws \App\Exception\DemandForecastServiceException
     */
    public function getARIMAPredictionFromFile(DemandForecastFileDto $request): void
    {
        // Формируем данные для анализа
        $cFile = curl_file_create($this->pathToFile . '/' . $request->file);
        $postData = array('file'=> $cFile);

        // Формируем запрос в сервис
        $this->uri = $this->baseUri . 'arima/prediction';

        if ($request->freq !== "") {
            $this->addProperty("freq", $request->freq);
        }
        if ($request->column !== "") {
            $this->addProperty("column", $request->column);
        }
        if ($request->delimiter !== "") {
            $this->addProperty("delimiter", $request->delimiter);
        }
        if ($request->period !== "") {
            $this->addProperty("period", $request->period);
        }
        if ($request->seasonal !== "") {
            $this->addProperty("seasonal", $request->period);
        }

        // Создаем запрос в сервис
        $ch = curl_init($this->uri);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $responseData = curl_exec($ch);

        // Выбрасываем ошибку. Сервис недоступен
        if ($responseData === false) {
            throw new DemandForecastServiceException('Сервис временно недоступен. 
            Попробуйте создать отчет о прогнозировании спроса позже позднее');
        }

        curl_close($ch);

        $result = json_decode($responseData, true);
        if (isset($result['code']) && $result['code'] === 403) {
            throw new DemandForecastServiceException($result['message']);
        }

        if (!is_dir($this->pathToSave)
            && !mkdir($this->pathToSave, 0777, true)
            && !is_dir($this->pathToSave)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $this->pathToSave));
        }

        // Сохранение результата в ФС
        file_put_contents(
            $this->pathToSave . '/' . $request->filename . '.json',
            $responseData
        );
    }

    private function addProperty($property, $param): void
    {
        if ($this->first_position) {
            $this->uri .= "?" . $property . "=" . $param;
            $this->first_position = false;
        } else {
            $this->uri .= "&" . $property . "=" . $param;
        }
    }
}
