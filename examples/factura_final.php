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
    
    echo "=== FACTURA FINAL CORREGIDA ===\n\n";
    
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
    $record->invoiceType = InvoiceType::F1;  // Objeto, no string
    $record->description = 'Factura de prueba VeriFactu';
    
    // Receptor
    $recipient = new FiscalIdentifier('Cliente Test SL', 'B87654321');
    $record->recipients = [$recipient];
    
    // Importes
    $record->totalAmount = 121.00;
    $record->totalTaxAmount = 21.00;
    
    echo "Número: $numeroFactura\n";
    echo "Total: 121.00 €\n";
    echo "Tipo: F1 (objeto InvoiceType)\n";
    
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