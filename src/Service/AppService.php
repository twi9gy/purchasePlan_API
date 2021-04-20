<?php

namespace App\Service;

use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

class AppService
{
    private $baseUri = 'http://purchase_plan.local/api/v1/auth/signin';

    public function login(string $data): array
    {
        // Формирование запроса в сервис Billing
        $ch = curl_init($this->baseUri);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data)
        ]);
        $response = curl_exec($ch);
        if ($response === false) {
            throw new CustomUserMessageAuthenticationException('Сервис временно недоступен. 
            Попробуйте создать авторизоваться позднее.');
        }
        curl_close($ch);

        // Парсер ответа сервиса
        return json_decode($response, true);
    }
}
