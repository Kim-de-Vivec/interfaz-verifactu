<?php
require_once __DIR__ . '/../vendor/autoload.php';

use josemmo\Verifactu\Models\Records\RegistrationRecord;
use josemmo\Verifactu\Models\Records\InvoiceIdentifier;
use josemmo\Verifactu\Models\Records\FiscalIdentifier;

try {
    $configData = require __DIR__ . '/../config/verifactu_simple.php';
    $client = $configData['client'];
    $system = $configData['system'];
    $config = $configData['config'];
    
    echo "=== FACTURA CON API REAL ===\n\n";
    
    // Crear registro
    $record = new RegistrationRecord();
    
    // Identificador de factura
    $numeroFactura = 'FAC-TEST-' . date('Ymd-His');
    $invoiceId = new InvoiceIdentifier(
        $config['empresa']['nif'],
        $numeroFactura,
        new DateTimeImmutable()
    );
    
    // Propiedades según la API real
    $record->invoiceId = $invoiceId;
    $record->issuerName = $config['empresa']['nombre'];
    $record->invoiceType = 'F1';
    $record->description = 'Factura de prueba VeriFactu';
    
    // Receptores (array)
    $recipient = new FiscalIdentifier('Cliente Test SL', 'B87654321');
    $record->recipients = [$recipient];
    
    // Importes
    $record->totalAmount = 121.00;
    $record->totalTaxAmount = 21.00;
    
    // Breakdown (desglose)
    $record->breakdown = [
        [
            'taxableBase' => 100.00,
            'taxRate' => 21.00,
            'taxAmount' => 21.00
        ]
    ];
    
    echo "Número: $numeroFactura\n";
    echo "Total: 121.00 €\n";
    echo "Configuración completada\n";
    
    // Intentar envío
    echo "\nEnviando a AEAT...\n";
    $response = $client->submitRecord($record);
    
    if ($response->isAccepted()) {
        echo "ÉXITO: Factura aceptada\n";
        echo "CSV: " . $response->getCsv() . "\n";
    } else {
        echo "ERROR: Factura rechazada\n";
        foreach ($response->getErrors() as $error) {
            echo "- $error\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "En: " . $e->getFile() . " línea " . $e->getLine() . "\n";
}
