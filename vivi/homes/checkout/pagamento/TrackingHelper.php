<?php
/**
 * TrackingHelper.php - Auxiliar para persistência de IDs de rastreamento e contexto.
 */

class TrackingHelper {
    private static $contextDir = __DIR__ . '/context';

    /**
     * Obtém ou gera um External ID persistente.
     */
    public static function getExternalId() {
        if (isset($_SESSION['external_id'])) {
            return $_SESSION['external_id'];
        }

        if (isset($_COOKIE['external_id'])) {
            $_SESSION['external_id'] = $_COOKIE['external_id'];
            return $_COOKIE['external_id'];
        }

        $id = bin2hex(random_bytes(16));
        setcookie('external_id', $id, time() + (86400 * 30), '/'); // 30 dias
        $_SESSION['external_id'] = $id;
        return $id;
    }

    /**
     * Captura FBP e FBC dos cookies.
     */
    public static function getFacebookIds() {
        return [
            'fbp' => $_COOKIE['_fbp'] ?? null,
            'fbc' => $_COOKIE['_fbc'] ?? null
        ];
    }

    /**
     * Salva o contexto de rastreamento associado a uma transação.
     * Útil para o Webhook recuperar os dados originais do navegador.
     */
    public static function saveContext(string $transactionId, array $data) {
        if (!is_dir(self::$contextDir)) {
            mkdir(self::$contextDir, 0775, true);
        }

        $context = array_merge([
            'external_id' => self::getExternalId(),
            'fbp' => $_COOKIE['_fbp'] ?? null,
            'fbc' => $_COOKIE['_fbc'] ?? null,
            'ip' => self::getClientIp(),
            'ua' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'timestamp' => time()
        ], $data);

        $path = self::$contextDir . '/' . preg_replace('/[^a-zA-Z0-9_\-]/', '', $transactionId) . '.json';
        file_put_contents($path, json_encode($context));
    }

    /**
     * Recupera o contexto de rastreamento.
     */
    public static function loadContext(string $transactionId) {
        $path = self::$contextDir . '/' . preg_replace('/[^a-zA-Z0-9_\-]/', '', $transactionId) . '.json';
        if (file_exists($path)) {
            return json_decode(file_get_contents($path), true);
        }
        return null;
    }

    public static function getClientIp() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        }
        return $_SERVER['REMOTE_ADDR'] ?? '';
    }
}
