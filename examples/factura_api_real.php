<?php
require_once __DIR__ . '/../vendor/autoload.php';

use josemmo\Verifactu\Models\Records\RegistrationRecord;
use josemmo\Verifactu\Models\Records\InvoiceIdentifier;
use josemmo\Verifactu\Models\Records\FiscalIdentifier;
use josemmo\Verifactu\Models\Records\InvoiceType;

try {
    $configData = require __DIR__ . '/../config/verifactu_simple.php';
    $client = $configData['client'];
    $system = $configData['system'];
    $config = $configData['config'];
    
    echo "=== FACTURA CON API REAL DEL CLIENTE ===\n\n";
    
    // Crear registro
    $record = new RegistrationRecord();
    
    // Identificador
    $numeroFactura = 'FAC-TEST-' . date('Ymd-His');
    $invoiceId = new InvoiceIdentifier(
        $config['empresa']['nif'],
        $numeroFactura,
        new DateTimeImmutable()
    );
    
    $record->invoiceId = $invoiceId;
    $record->issuerName = $config['empresa']['nombre'];
    
    // Intentar crear el InvoiceType
    $record->invoiceType = new InvoiceType('F1');
    
    $record->description = 'Factura de prueba';
    
    // Receptor
    $recipient = new FiscalIdentifier('Cliente Test', 'B87654321');
    $record->recipients = [$recipient];
    
    // Importes
    $record->totalAmount = 121.00;
    $record->totalTaxAmount = 21.00;
    
    echo "Configuración completada\n";
    echo "Número: $numeroFactura\n";
    
    // Usar el método correcto del cliente
    echo "\nEnviando con sendRegistrationRecords()...\n";
    $response = $client->sendRegistrationRecords([$record]);
    
    echo "Respuesta recibida\n";
    var_dump($response);
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . " línea " . $e->getLine() . "\n";
}


