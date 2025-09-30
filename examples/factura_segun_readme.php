<?php
require_once __DIR__ . '/../vendor/autoload.php';

use josemmo\Verifactu\Models\Records\RegistrationRecord;
use josemmo\Verifactu\Models\Records\InvoiceIdentifier;
use josemmo\Verifactu\Models\Records\FiscalIdentifier;
use josemmo\Verifactu\Models\Records\InvoiceType;
use josemmo\Verifactu\Models\Records\BreakdownDetails;
use josemmo\Verifactu\Models\Records\TaxType;
use josemmo\Verifactu\Models\Records\RegimeType;
use josemmo\Verifactu\Models\Records\OperationType;
use josemmo\Verifactu\Models\ComputerSystem;

try {
    $configData = require __DIR__ . '/../config/verifactu_simple.php';
    $config = $configData['config'];
    
    echo "=== FACTURA SEGÚN README OFICIAL ===\n\n";
    
    // Generar registro de facturación
    $record = new RegistrationRecord();
    $record->invoiceId = new InvoiceIdentifier();
    $record->invoiceId->issuerId = $config['empresa']['nif'];
    $record->invoiceId->invoiceNumber = 'FAC-' . date('Ymd-His');
    $record->invoiceId->issueDate = new DateTimeImmutable();
    $record->issuerName = $config['empresa']['nombre'];
    $record->invoiceType = InvoiceType::Simplificada;  // Enum, no new!
    $record->description = 'Factura de prueba VeriFactu';
    
    // Desglose (breakdown)
    $record->breakdown[0] = new BreakdownDetails();
    $record->breakdown[0]->taxType = TaxType::IVA;
    $record->breakdown[0]->regimeType = RegimeType::C01;
    $record->breakdown[0]->operationType = OperationType::S1;
    $record->breakdown[0]->taxRate = '21.00';
    $record->breakdown[0]->baseAmount = '100.00';
    $record->breakdown[0]->taxAmount = '21.00';
    
    $record->totalTaxAmount = '21.00';
    $record->totalAmount = '121.00';
    
    // Primera factura de la cadena
    $record->previousInvoiceId = null;
    $record->previousHash = null;
    $record->hashedAt = new DateTimeImmutable();
    $record->hash = $record->calculateHash();
    
    // Validar
    $record->validate();
    
    echo "Número: {$record->invoiceId->invoiceNumber}\n";
    echo "Total: {$record->totalAmount} €\n";
    
    // Definir datos del SIF
    $system = new ComputerSystem();
    $system->vendorName = $config['empresa']['nombre'];
    $system->vendorNif = $config['empresa']['nif'];
    $system->name = 'Sistema VeriFactu PHP';
    $system->id = 'D1';  // Máximo 2 caracteres
    $system->version = '1.0.0';
    $system->installationNumber = '0001';
    $system->onlySupportsVerifactu = true;
    $system->supportsMultipleTaxpayers = false;
    $system->hasMultipleTaxpayers = false;
    $system->validate();
    
    // Crear cliente AEAT
    $taxpayer = new FiscalIdentifier($config['empresa']['nombre'], $config['empresa']['nif']);
    $client = new \josemmo\Verifactu\Services\AeatClient(
        $system,
        $taxpayer,
        $config['certificate_path'],
        $config['certificate_password']
    );
    $client->setProduction(false); // Sandbox
    
    echo "\nEnviando a AEAT (sandbox)...\n";
    $response = $client->send([$record]);
    
    echo "\nRespuesta recibida:\n";
    echo $response->asXML() . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . " línea " . $e->getLine() . "\n";
}


