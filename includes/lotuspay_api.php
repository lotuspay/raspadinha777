<?php
// LotusPay API Client - stores token and base URL in code as requested

class LotusPayAPI {
    private const BASE_URL = 'https://api.lotuspay.me';
    private const API_TOKEN = 'lp_7878702b30ad6c809c7e5302199d44c9f90157d188fd151eeb702bb15e889212';

    private function request(string $method, string $path, ?array $body = null) {
        $url = rtrim(self::BASE_URL, '/') . $path;
        $ch = curl_init($url);

        $headers = [
            'Lotuspay-Auth: ' . self::API_TOKEN,
            'Accept: application/json',
            'Content-Type: application/json'
        ];

        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 30
        ];

        if ($body !== null) {
            $options[CURLOPT_POSTFIELDS] = json_encode($body);
        }

        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception('Erro cURL: ' . $error);
        }

        $data = json_decode($response, true);
        if ($httpCode < 200 || $httpCode >= 300) {
            $msg = is_array($data) ? json_encode($data) : (string)$response;
            throw new Exception('Erro HTTP ' . $httpCode . ': ' . $msg);
        }

        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Resposta JSON inválida: ' . $response);
        }

        return $data;
    }

    // Cash-in: cria cobrança PIX para depósito
    public function cashIn(array $payload) {
        return $this->request('POST', '/api/v1/cashin', $payload);
    }

    // Cash-out: solicita saque PIX
    public function cashOut(array $payload) {
        return $this->request('POST', '/api/v1/cashout', $payload);
    }

    // Consulta status por publicId
    public function getTransactionStatus(string $publicId) {
        $path = '/api/v1/transactions/' . rawurlencode($publicId);
        return $this->request('GET', $path);
    }
}
