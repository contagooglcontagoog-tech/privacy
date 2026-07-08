<?php
/**
 * FacebookCAPI.php - Helper para enviar eventos à API de Conversões do Facebook.
 */

class FacebookCAPI {
    private $pixelId;
    private $accessToken;
    private $logFile;

    public function __construct(string $pixelId, string $accessToken) {
        $this->pixelId = $pixelId;
        $this->accessToken = $accessToken;
        $this->logFile = __DIR__ . '/facebook_capi.log';
    }

    /**
     * Envia um evento para o Facebook CAPI.
     */
    public function sendEvent(string $eventName, array $userData, array $customData = [], string $eventSourceUrl = null, string $eventId = null) {
        $eventTime = time();
        
        // Dados do usuário (Hashed conforme documentação)
        $hashedUserData = $this->prepareUserData($userData);
        
        $eventData = [
            'event_name' => $eventName,
            'event_time' => $eventTime,
            'event_source_url' => $eventSourceUrl ?? $this->getCurrentUrl(),
            'action_source' => 'website',
            'user_data' => $hashedUserData,
            'custom_data' => $customData,
        ];

        if ($eventId) {
            $eventData['event_id'] = $eventId;
        }

        $payload = [
            'data' => [ $eventData ],
            'access_token' => $this->accessToken
        ];

        $url = "https://graph.facebook.com/v18.0/{$this->pixelId}/events";

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 30
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $this->log("Evento: {$eventName} | HTTP: {$httpCode} | Response: {$response}");

        return json_decode($response, true);
    }

    /**
     * Normaliza string para hash (lowercase, trim).
     */
    private function normalize($value) {
        return strtolower(trim((string)$value));
    }

    /**
     * Prepara e faz o hash dos dados do usuário.
     */
    private function prepareUserData(array $data): array {
        $ready = [];

        // E-mail (Normalizar e Hash)
        if (!empty($data['email'])) {
            $ready['em'] = hash('sha256', strtolower(trim($data['email'])));
        }

        // Telefone (Normalizar e Hash - Remover caracteres não numéricos)
        if (!empty($data['phone'])) {
            $phone = preg_replace('/\D/', '', $data['phone']);
            $ready['ph'] = hash('sha256', $phone);
        }

        // Nome (Primeiro Nome e Sobrenome - Hash)
        if (!empty($data['name'])) {
            $parts = explode(' ', trim($data['name']));
            $ready['fn'] = hash('sha256', strtolower($parts[0]));
            if (count($parts) > 1) {
                $ready['ln'] = hash('sha256', strtolower(end($parts)));
            }
        }

        // IP do cliente (Permite override se passado nos dados)
        $ready['client_ip_address'] = $data['client_ip_address'] ?? $this->getClientIp();

        // User Agent
        $ready['client_user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';

        // Cidade/UF (Geo Loc se disponível)
        if (!empty($data['city'])) {
            $ready['ct'] = hash('sha256', $this->normalize($data['city']));
        }
        if (!empty($data['state'])) {
            $ready['st'] = hash('sha256', $this->normalize($data['state']));
        }
        if (!empty($data['zip'])) {
            $ready['zp'] = hash('sha256', preg_replace('/\D/', '', $data['zip']));
        }
        if (!empty($data['country'])) {
            $ready['country'] = hash('sha256', $this->normalize($data['country']));
        }

        // Gênero e Data de Nascimento
        if (!empty($data['gender'])) {
            $ready['ge'] = hash('sha256', $this->normalize($data['gender']));
        }
        if (!empty($data['db'])) {
            // Facebook espera YYYYMMDD
            $db = preg_replace('/\D/', '', $data['db']);
            if (strlen($db) === 8) {
                $ready['db'] = hash('sha256', $db);
            }
        }

        // External ID
        if (!empty($data['external_id'])) {
            // IMPORTANTE: external_id NÃO deve ser em hash SHA256 conforme as melhores práticas recentes para evitar erros de match
            // A Meta recomenda enviar o ID original se ele for um identificador único estável
            $ready['external_id'] = (string)$data['external_id'];
        }

        // FBC / FBP (Cookies do Facebook se existirem)
        if (isset($_COOKIE['_fbc'])) {
            $ready['fbc'] = $_COOKIE['_fbc'];
        }
        if (isset($_COOKIE['_fbp'])) {
            $ready['fbp'] = $_COOKIE['_fbp'];
        }

        return $ready;
    }

    private function getClientIp() {
        $ips = [];
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IPV6'])) {
            $ips[] = $_SERVER['HTTP_CF_CONNECTING_IPV6'];
        }
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            $ips[] = $_SERVER['HTTP_CF_CONNECTING_IP'];
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $parts = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            foreach ($parts as $p) {
                $ips[] = trim($p);
            }
        }
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ips[] = $_SERVER['HTTP_CLIENT_IP'];
        }
        if (!empty($_SERVER['REMOTE_ADDR'])) {
            $ips[] = $_SERVER['REMOTE_ADDR'];
        }

        // Prioritize IPv6 as recommended by Facebook
        foreach ($ips as $ip) {
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                return $ip;
            }
        }
        foreach ($ips as $ip) {
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                return $ip;
            }
        }
        return $_SERVER['REMOTE_ADDR'] ?? '';
    }

    private function getCurrentUrl() {
        return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    }

    private function log($msg) {
        file_put_contents($this->logFile, '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL, FILE_APPEND);
    }
}
