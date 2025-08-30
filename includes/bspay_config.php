<?php
require_once __DIR__ . '/db.php';

class BSPayConfig {
    // Credenciais BSPay - PRODUÇÃO
    private static $client_id = 'testeadasda';
    private static $client_secret = 'admin1234567890123456';
    private static $webhook_url = 'https://localhost/webhook_bspay_novo.php';
    private static $base_url = 'https://api.diagnostico.com/v2';
    
    // Flag para indicar se as configurações foram carregadas do banco
    private static $loaded_from_db = false;
    
    // Getters
    public static function getClientId() {
        self::loadFromDatabase();
        return self::$client_id;
    }
    
    public static function getClientSecret() {
        self::loadFromDatabase();
        return self::$client_secret;
    }
    
    public static function getWebhookUrl() {
        self::loadFromDatabase();
        return self::$webhook_url;
    }
    
    public static function getBaseUrl() {
        self::loadFromDatabase();
        return self::$base_url;
    }
    
    // Setters
    public static function setClientId($client_id) {
        self::$client_id = $client_id;
        return self::persistConfig();
    }
    
    public static function setClientSecret($client_secret) {
        self::$client_secret = $client_secret;
        return self::persistConfig();
    }
    
    public static function setWebhookUrl($webhook_url) {
        self::$webhook_url = $webhook_url;
        return self::persistConfig();
    }
    
    public static function setBaseUrl($base_url) {
        self::$base_url = $base_url;
        return self::persistConfig();
    }
    
    // Método para carregar configurações do banco de dados
    private static function loadFromDatabase() {
        if (self::$loaded_from_db) {
            return;
        }
        
        global $conn;
        if (!$conn) {
            return;
        }
        
        try {
            $stmt = $conn->prepare("SELECT chave, valor FROM configuracoes WHERE chave IN ('bspay_client_id', 'bspay_client_secret', 'bspay_webhook_url', 'bspay_base_url')");
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_assoc()) {
                switch ($row['chave']) {
                    case 'bspay_client_id':
                        self::$client_id = $row['valor'];
                        break;
                    case 'bspay_client_secret':
                        self::$client_secret = $row['valor'];
                        break;
                    case 'bspay_webhook_url':
                        self::$webhook_url = $row['valor'];
                        break;
                    case 'bspay_base_url':
                        self::$base_url = $row['valor'];
                        break;
                }
            }
            
            $stmt->close();
            self::$loaded_from_db = true;
        } catch (Exception $e) {
            // Em caso de erro, mantém os valores padrão do arquivo
            error_log("Erro ao carregar configurações BSPay do banco: " . $e->getMessage());
        }
    }
    
    // Método para persistir as configurações no arquivo PHP e banco de dados
    private static function persistConfig() {
        $success_file = self::persistToFile();
        $success_db = self::persistToDatabase();
        
        return $success_file && $success_db;
    }
    
    // Método para persistir no arquivo PHP
    private static function persistToFile() {
        $filePath = __FILE__;
        $content = file_get_contents($filePath);
        
        // Atualiza as variáveis estáticas no arquivo PHP
        $content = preg_replace(
            '/private static \$client_id = \'[^\']*\';/',
            "private static \$client_id = '" . addslashes(self::$client_id) . "';",
            $content
        );
        
        $content = preg_replace(
            '/private static \$client_secret = \'[^\']*\';/',
            "private static \$client_secret = '" . addslashes(self::$client_secret) . "';",
            $content
        );
        
        $content = preg_replace(
            '/private static \$webhook_url = \'[^\']*\';/',
            "private static \$webhook_url = '" . addslashes(self::$webhook_url) . "';",
            $content
        );
        
        $content = preg_replace(
            '/private static \$base_url = \'[^\']*\';/',
            "private static \$base_url = '" . addslashes(self::$base_url) . "';",
            $content
        );
        
        return file_put_contents($filePath, $content) !== false;
    }
    
    // Método para persistir no banco de dados
    private static function persistToDatabase() {
        global $conn;
        if (!$conn) {
            return false;
        }
        
        try {
            $configs = [
                'bspay_client_id' => self::$client_id,
                'bspay_client_secret' => self::$client_secret,
                'bspay_webhook_url' => self::$webhook_url,
                'bspay_base_url' => self::$base_url
            ];
            
            $stmt = $conn->prepare("INSERT INTO configuracoes (chave, valor) VALUES (?, ?) ON DUPLICATE KEY UPDATE valor = VALUES(valor)");
            
            foreach ($configs as $chave => $valor) {
                $stmt->bind_param('ss', $chave, $valor);
                $stmt->execute();
            }
            
            $stmt->close();
            return true;
        } catch (Exception $e) {
            error_log("Erro ao salvar configurações BSPay no banco: " . $e->getMessage());
            return false;
        }
    }
    
    // Método para carregar configurações (não necessário mais, pois as configurações estão no próprio arquivo)
    public static function loadConfig() {
        // As configurações agora são carregadas diretamente das variáveis estáticas
        // Este método é mantido para compatibilidade
        return true;
    }
    
    // Método para salvar as configurações no arquivo (mantido para compatibilidade)
    public static function saveToFile() {
        return self::persistConfig();
    }
}

// As configurações são carregadas automaticamente das variáveis estáticas
?>