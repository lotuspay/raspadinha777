<?php
class BSPayConfig {
    // Credenciais BSPay - PRODUÇÃO
    private static $client_id = 'profelar2025_3007001185578910';
    private static $client_secret = 'dddb10361aa6d93746c63c9c177b15ca875bbe7e4c30cb270e92c0e344c39d0a';
    private static $webhook_url = 'https://web.profelardev.site/webhook_bspay_novo.php';
    
    public static function getClientId() {
        return self::$client_id;
    }
    
    public static function getClientSecret() {
        return self::$client_secret;
    }
    
    public static function getWebhookUrl() {
        return self::$webhook_url;
    }
}
?>
