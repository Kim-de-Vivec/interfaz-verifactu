<?php
require_once __DIR__ . '/../vendor/autoload.php';

use josemmo\Verifactu\Models\ComputerSystem;
use josemmo\Verifactu\Services\AeatClient;
use josemmo\Verifactu\Models\Records\FiscalIdentifier;

// Cargar variables de entorno
function env($key, $default = null)
{
    return $_ENV[$key] ?? $default;
}

if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && !str_starts_with(trim($line), '#')) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

// Configurar OpenSSL
if (file_exists(__DIR__ . '/../openssl_legacy.cnf')) {
    putenv("OPENSSL_CONF=" . __DIR__ . '/../openssl_legacy.cnf');
}

try {
    // Configuración básica
    $config = [
        'environment' => env('VERIFACTU_ENV', 'development'),
        'production' => env('VERIFACTU_ENV', 'development') === 'production',
        'certificate_path' => env('VERIFACTU_CERT_PATH'),
        'certificate_password' => env('VERIFACTU_CERT_PASSWORD'),
        'empresa' => [
            'nif' => env('EMPRESA_NIF'),
            'nombre' => env('EMPRESA_NOMBRE')
        ],
        'desarrollador' => [
            'nif' => env('DESARROLLADOR_NIF'),
            'nombre' => env('DESARROLLADOR_NOMBRE')
        ]
    ];

    // Sistema informático (propiedades públicas)
    $system = new ComputerSystem();
    $system->nif = $config['empresa']['nif'];
    $system->name = 'Sistema VeriFactu Simple';
    $system->version = '2.0.0';
    $system->installation_id = $config['production'] ? 'PROD001' : 'DEV001';
    $system->developer_nif = $config['desarrollador']['nif'];
    $system->developer_name = $config['desarrollador']['nombre'];

    // Taxpayer (empresa)
    $taxpayer = new FiscalIdentifier(
        $config['empresa']['nombre'],
        $config['empresa']['nif']
    );

    // Cliente AEAT
    $client = new AeatClient(
        $system,
        $taxpayer,
        $config['certificate_path'],
        $config['certificate_password']
    );

    return [
        'config' => $config,
        'client' => $client,
        'system' => $system,
        'taxpayer' => $taxpayer
    ];
} catch (Exception $e) {
    throw new Exception("Error en configuración: " . $e->getMessage());
}
