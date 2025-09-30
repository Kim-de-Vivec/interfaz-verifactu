<?php
require_once __DIR__ . '/../vendor/autoload.php';

try {
    $configData = require __DIR__ . '/../config/verifactu_simple.php';
    $client = $configData['client'];
    $system = $configData['system'];
    $config = $configData['config'];
    
    echo "=== TEST MÍNIMO ===\n";
    echo "Cliente: " . (isset($client) ? 'OK' : 'NO') . "\n";
    echo "Sistema: " . (isset($system) ? 'OK' : 'NO') . "\n";
    echo "Config: " . (isset($config) ? 'OK' : 'NO') . "\n";
    
    // Verificar si el cliente tiene métodos disponibles
    $reflection = new ReflectionClass($client);
    echo "\nMétodos del cliente AEAT:\n";
    foreach ($reflection->getMethods() as $method) {
        if ($method->isPublic() && !$method->isStatic()) {
            echo "- " . $method->getName() . "()\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}


