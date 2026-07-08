<?php

class Cloaker {
    
    // 1. Identificador Primário: Regex de User-Agent (Bots)
    private static $uaRegex = '/(facebook|meta|whatsapp|instagram)/i';

    // Lista de User-Agents específicos (Bots)
    private static $botSignatures = [
        'facebookexternalhit', 'facebookcatalog', 'facebookplatform', 'facebot', 'googlebot', 
        'twitterbot', 'linkedinbot', 'adsbot-google', 'mj12bot', 'ahrefsbot',
        'okhttp', 'scanner', 'assetnote', 'unknown'
    ];

    // 2. Identificador Estratégico: ASNs do Meta
    private static $blockedASNs = [
        'AS32934', 'AS63293', 'AS135351'
    ];
    
    // Arquivo de Log
    private static $logFile = 'cloaker_access.log';
    
    // IP Data Cache
    private static $ipData = null;

    /**
     * Main protection method.
     * Enforces: No Bots, Only Mobile, Only Brazil.
     * Logs all attempts.
     */
    public static function protect() {
        
        // 1. Bloqueia Bots por Assinatura (User-Agent)
        if (self::isBotUA()) {
            self::logAccess('BLOCKED', 'Bot Signature (User-Agent)');
            self::redirectToSafePage();
        }

        // 2. Bloqueia Desktop (Só Mobile Permitido)
        if (!self::isMobile()) {
            self::logAccess('BLOCKED', 'Desktop Device Detected');
            // Uncomment line below to enforce mobile check
            self::redirectToSafePage();
        }

        // Fetch IP Data once (ASN + Country)
        self::fetchIPData();

        // 3. Bloqueia por Geo (Só Brasil Permitido)
        if (!self::isBrazil()) {
             $country = self::$ipData['countryCode'] ?? 'UNKNOWN';
             self::logAccess('BLOCKED', "Geo Mismatch ({$country})");
             self::redirectToSafePage();
        }

        // 4. Bloqueia por Rede do Facebook (ASN)
        if (self::isBotNetwork()) {
            $asn = self::$ipData['as'] ?? 'UNKNOWN';
            self::logAccess('BLOCKED', "ASN Blacklist ({$asn})");
            self::redirectToSafePage();
        }

        // Se passar por tudo
        self::sendPushcutNotification();
        self::logAccess('ALLOWED', 'Clean Traffic');
    }

    private static function redirectToSafePage() {
        header("Location: safe_page.php");
        exit;
    }

    /**
     * Logs access details to file.
     */
    private static function logAccess($status, $reason) {
        $ip = self::getClientIP();
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $country = self::$ipData['countryCode'] ?? 'N/A';
        $date = date('Y-m-d H:i:s');
        
        $logEntry = "[$date]IP: $ip | Status: $status | Reason: $reason | Country: $country | UA: $ua" . PHP_EOL;
        
        // Append to log file
        file_put_contents(self::$logFile, $logEntry, FILE_APPEND);
    }

    /**
     * Checks if User-Agent is Mobile.
     */
    private static function isMobile() {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        // Strict Mobile Rule: Apenas Android e iPhone/iPad
        return preg_match('/(android|iphone|ipad|ipod)/i', $userAgent);
    }

    private static function isBotUA() {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        if (empty($userAgent)) return true;

        // WHITELIST: Permitir explicitamente tráfego Mobile (Android/iPhone) vindo de Redes Sociais
        // Isso garante que o navegador interno do Instagram/Facebook/WhatsApp passe.
        // Adicionado: FBIOS, FBAN/EMA conforme solicitado.
        if (preg_match('/(android|iphone|ipad)/i', $userAgent)) {
            if (preg_match('/(instagram|whatsapp|fbav|fb_iab|fb4a|fbios|fban|ema)/i', $userAgent)) {
                return false; // É um usuário mobile real no app
            }
        }

        // Remover a verificação genérica que bloqueia apps legítimos do Facebook/Instagram
        // if (preg_match(self::$uaRegex, $userAgent)) return true;

        $userAgentLower = strtolower($userAgent);
        foreach (self::$botSignatures as $signature) {
            if (strpos($userAgentLower, $signature) !== false) return true;
        }
        return false;
    }

    /**
     * Fetches IP data (ASN, Country) from API if not already fetched.
     */
    private static function fetchIPData() {
        if (self::$ipData !== null) return;

        $ip = self::getClientIP();
        
        // Skip for localhost dev
        if ($ip == '127.0.0.1' || $ip == '::1') {
            self::$ipData = ['status' => 'success', 'countryCode' => 'BR', 'as' => '']; 
            return;
        }

        $ctx = stream_context_create(['http'=> ['timeout' => 2]]);
        // Fetch countryCode and AS
        $json = @file_get_contents("http://ip-api.com/json/{$ip}?fields=status,countryCode,as", false, $ctx);
        
        if ($json) {
            self::$ipData = json_decode($json, true);
        } else {
            // Tenta API Secundária (Fallback)
            $json2 = @file_get_contents("http://ipwhois.app/json/{$ip}", false, $ctx);
            if ($json2) {
                 $data = json_decode($json2, true);
                 // Map ipwhois structure to our standard
                 self::$ipData = [
                     'countryCode' => $data['country_code'] ?? 'UNKNOWN',
                     'as' => $data['asn'] ?? '' // ipwhois returns 'asn' like "AS12345 Organization"
                 ];
            } else {
                 // STRICT MODE: Se ambas falharem, BLOQUEIA.
                 // "Não autorize de jeito nenhum" se não tiver certeza que é BR.
                 self::$ipData = ['countryCode' => 'UNKNOWN', 'as' => '']; 
                 self::logAccess('BLOCKED', 'Geo API Failure (Strict Mode)');
            }
        }
    }

    /**
     * Checks if IP is from Brazil.
     */
    private static function isBrazil() {
        if (isset(self::$ipData['countryCode']) && self::$ipData['countryCode'] === 'BR') {
            return true;
        }
        return false;
    }

    /**
     * Checks if IP belongs to blocked ASN.
     */
    private static function isBotNetwork() {
        if (isset(self::$ipData['as'])) {
            foreach (self::$blockedASNs as $asn) {
                if (strpos(self::$ipData['as'], $asn) !== false) {
                    return true;
                }
            }
        }
        return false;
    }

    private static function sendPushcutNotification() {
        $url = 'https://api.pushcut.io/Dhaa7jDokLKD48hZmOJeL/notifications/Privy%20';
        $ctx = stream_context_create(['http'=> ['timeout' => 2]]);
        @file_get_contents($url, false, $ctx);
    }

    private static function getClientIP() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return $_SERVER['HTTP_X_FORWARDED_FOR'];
        return $_SERVER['REMOTE_ADDR'];
    }
}
