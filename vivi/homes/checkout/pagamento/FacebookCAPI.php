<?php
/**
 * FacebookCAPI.php - Helper para enviar eventos à API de Conversões do Facebook.
 * Versão Otimizada para Match Quality Máximo.
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
            'data' => [ $eventData ]
        ];

        $url = "https://graph.facebook.com/v18.0/{$this->pixelId}/events?access_token={$this->accessToken}";

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
            // Se não começar com o código do país, assume 55 (Brasil)
            if (strlen($phone) <= 11) {
                $phone = '55' . $phone;
            }
            $ready['ph'] = hash('sha256', $phone);
        }

        // Nome (Primeiro Nome e Sobrenome - Hash)
        if (!empty($data['name'])) {
            $name = trim($data['name']);
            $parts = explode(' ', $name);
            $ready['fn'] = hash('sha256', strtolower(trim($parts[0])));
            if (count($parts) > 1) {
                $ready['ln'] = hash('sha256', strtolower(trim(end($parts))));
            }
        } elseif (!empty($data['first_name'])) {
            $ready['fn'] = hash('sha256', strtolower(trim($data['first_name'])));
            if (!empty($data['last_name'])) {
                $ready['ln'] = hash('sha256', strtolower(trim($data['last_name'])));
            }
        }

        // Cidade, Estado, CEP e País
        if (!empty($data['city'])) {
            $ready['ct'] = hash('sha256', strtolower(trim($data['city'])));
        }
        if (!empty($data['state'])) {
            $ready['st'] = hash('sha256', strtolower(trim($data['state'])));
        }
        if (!empty($data['zip'])) {
            $ready['zp'] = hash('sha256', preg_replace('/\D/', '', $data['zip']));
        }
        if (!empty($data['country'])) {
            // Facebook espera código do país em minúsculas (ex: br)
            $country = strtolower(trim($data['country']));
            if (strlen($country) > 2) {
                // Se for "Brasil", tenta converter ou apenas usa o hash (melhor usar código de 2 letras)
                if ($country === 'brasil' || $country === 'brazil') $country = 'br';
            }
            $ready['country'] = hash('sha256', $country);
        } else {
            // Default para brasil se não informado
            $ready['country'] = hash('sha256', 'br');
        }

        // Gênero e Data de Nascimento (Opcionais)
        if (!empty($data['gender'])) {
            $ready['gen'] = hash('sha256', strtolower(trim($data['gender'][0]))); // 'm' ou 'f'
        }
        if (!empty($data['db'])) {
            $ready['db'] = hash('sha256', preg_replace('/\D/', '', $data['db'])); // YYYYMMDD
        }

        // Parâmetros de Identificação (NÃO HASHADOS)
        
        // IP do cliente
        $ready['client_ip_address'] = $data['client_ip_address'] ?? $this->getClientIp();

        // User Agent
        $ready['client_user_agent'] = $data['client_user_agent'] ?? ($_SERVER['HTTP_USER_AGENT'] ?? '');

        // External ID (Crucial para Match Quality)
        if (!empty($data['external_id'])) {
            $ready['external_id'] = $data['external_id'];
        }

        // FBC / FBP (Cookies do Facebook)
        $ready['fbc'] = $data['fbc'] ?? ($_COOKIE['_fbc'] ?? null);
        $ready['fbp'] = $data['fbp'] ?? ($_COOKIE['_fbp'] ?? null);

        // Remover nulos
        return array_filter($ready);
    }

    private function getClientIp() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        }
        return $_SERVER['REMOTE_ADDR'] ?? '';
    }

    private function getCurrentUrl() {
        return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    }

    private function log($msg) {
        if (!is_dir(dirname($this->logFile))) {
            mkdir(dirname($this->logFile), 0775, true);
        }
        file_put_contents($this->logFile, '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL, FILE_APPEND);
    }
}
