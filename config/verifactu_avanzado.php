<?php
// ============================================================================
// ARCHIVO: config/verifactu_avanzado.php
// DESCRIPCIÓN: Configuración avanzada completa para VeriFactu Fase 2
// ============================================================================

require_once __DIR__ . '/../vendor/autoload.php';

use josemmo\Verifactu\Models\ComputerSystem;
use josemmo\Verifactu\Services\AeatClient;

// ============================================================================
// CARGAR VARIABLES DE ENTORNO
// ============================================================================

function cargarVariablesEntorno() {
    $envFile = __DIR__ . '/../.env';
    if (!file_exists($envFile)) {
        throw new Exception("Archivo .env no encontrado. Ejecutar: cp .env.example .env");
    }
    
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && !str_starts_with(trim($line), '#')) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

// Cargar variables
cargarVariablesEntorno();

// Función helper mejorada
function env($key, $default = null) {
    $value = $_ENV[$key] ?? $default;
    
    // Convertir valores booleanos
    if (in_array(strtolower($value), ['true', 'false'])) {
        return strtolower($value) === 'true';
    }
    
    return $value;
}

// ============================================================================
// CONFIGURACIÓN PRINCIPAL
// ============================================================================

$config = [
    // Entorno
    'environment' => env('VERIFACTU_ENV', 'development'),
    'production' => env('VERIFACTU_ENV', 'development') === 'production',
    'debug' => env('VERIFACTU_DEBUG', true),
    
    // Certificados
    'certificate_path' => env('VERIFACTU_CERT_PATH', __DIR__ . '/../certificates/fnmt-test.pfx'),
    'certificate_password' => env('VERIFACTU_CERT_PASSWORD', ''),
    
    // OpenSSL Legacy (para certificados antiguos)
    'openssl_conf' => env('OPENSSL_CONF', __DIR__ . '/../openssl_legacy.cnf'),
    
    // Empresa
    'empresa' => [
        'nif' => env('EMPRESA_NIF', 'A12345678'),
        'nombre' => env('EMPRESA_NOMBRE', 'Mi Empresa SL'),
        'direccion' => env('EMPRESA_DIRECCION', 'Calle Principal 123'),
        'codigo_postal' => env('EMPRESA_CP', '28001'),
        'ciudad' => env('EMPRESA_CIUDAD', 'Madrid'),
        'provincia' => env('EMPRESA_PROVINCIA', 'Madrid'),
    ],
    
    // Desarrollador
    'desarrollador' => [
        'nif' => env('DESARROLLADOR_NIF', '12345678Z'),
        'nombre' => env('DESARROLLADOR_NOMBRE', 'Desarrollador'),
        'email' => env('DESARROLLADOR_EMAIL', 'dev@empresa.com'),
    ],
    
    // URLs de la AEAT
    'urls' => [
        'sandbox' => env('AEAT_SANDBOX_URL', 'https://prewww2.aeat.es'),
        'production' => env('AEAT_PRODUCTION_URL', 'https://www2.agenciatributaria.gob.es'),
    ],
    
    // Configuración de conexión
    'conexion' => [
        'timeout' => 30,
        'retry_attempts' => 3,
        'retry_delay' => 2, // segundos
    ],
    
    // Logging
    'logging' => [
        'enabled' => env('VERIFACTU_DEBUG', true),
        'path' => __DIR__ . '/../logs/',
        'level' => env('VERIFACTU_LOG_LEVEL', 'debug'),
        'max_files' => 30, // días
    ],
    
    // Facturación
    'facturacion' => [
        'serie_defecto' => env('SERIE_DEFECTO', 'FAC'),
        'numeracion_automatica' => true,
        'iva_defecto' => 21.00,
        'moneda' => 'EUR',
    ]
];

// ============================================================================
// LOGGER AVANZADO
// ============================================================================

class VeriFactuLogger {
    private $config;
    private $logPath;
    
    public function __construct($config) {
        $this->config = $config['logging'];
        $this->logPath = rtrim($this->config['path'], '/') . '/';
        
        if (!is_dir($this->logPath)) {
            mkdir($this->logPath, 0755, true);
        }
    }
    
    public function log($level, $message, $context = []) {
        if (!$this->config['enabled']) return;
        
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = empty($context) ? '' : ' ' . json_encode($context, JSON_UNESCAPED_UNICODE);
        $logLine = "[{$timestamp}] {$level}: {$message}{$contextStr}\n";
        
        // Log general
        file_put_contents(
            $this->logPath . 'verifactu-' . date('Y-m-d') . '.log',
            $logLine,
            FILE_APPEND | LOCK_EX
        );
        
        // Log específico para errores
        if (in_array(strtolower($level), ['error', 'critical'])) {
            file_put_contents(
                $this->logPath . 'errores-' . date('Y-m-d') . '.log',
                $logLine,
                FILE_APPEND | LOCK_EX
            );
        }
    }
    
    public function debug($message, $context = []) {
        if ($this->config['level'] === 'debug') {
            $this->log('DEBUG', $message, $context);
        }
    }
    
    public function info($message, $context = []) {
        $this->log('INFO', $message, $context);
    }
    
    public function warning($message, $context = []) {
        $this->log('WARNING', $message, $context);
    }
    
    public function error($message, $context = []) {
        $this->log('ERROR', $message, $context);
    }
    
    public function critical($message, $context = []) {
        $this->log('CRITICAL', $message, $context);
    }
}

// ============================================================================
// CONFIGURAR OPENSSL PARA ALGORITMOS LEGACY
// ============================================================================

if (!empty($config['openssl_conf']) && file_exists($config['openssl_conf'])) {
    putenv("OPENSSL_CONF={$config['openssl_conf']}");
}

// ============================================================================
// CLIENTE AEAT AVANZADO
// ============================================================================

try {
    // Logger
    $logger = new VeriFactuLogger($config);
    $logger->info('Sistema VeriFactu Fase 2 iniciado', [
        'environment' => $config['environment'],
        'version' => '2.0-fase2'
    ]);
    
    // Sistema informático
    $system = new ComputerSystem([
        'nif' => $config['empresa']['nif'],
        'name' => 'Sistema VeriFactu Avanzado',
        'version' => '2.0.0',
        'installation_id' => $config['production'] ? 'PROD001' : 'DEV001',
        'developer_nif' => $config['desarrollador']['nif'],
        'developer_name' => $config['desarrollador']['nombre'],
    ]);
    
    // Taxpayer (empresa emisora)
    $taxpayer = new \josemmo\Verifactu\Models\Records\FiscalIdentifier();
    $taxpayer->setNif($config['empresa']['nif']);
    $taxpayer->setName($config['empresa']['nombre']);
    
    // Cliente AEAT (API correcta)
    $client = new AeatClient(
        $system,
        $taxpayer,
        $config['certificate_path'],
        $config['certificate_password']
    );
    
    $logger->debug('Configuración completada exitosamente');
    
} catch (Exception $e) {
    if (isset($logger)) {
        $logger->critical('Error crítico en inicialización', [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);
    }
    throw $e;
}

// ============================================================================
// RETORNO DE CONFIGURACIÓN
// ============================================================================

return [
    'config' => $config,
    'logger' => $logger,
    'client' => $client,
    'system' => $system
];

?>
