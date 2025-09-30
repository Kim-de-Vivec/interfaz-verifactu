<?php
// Ejemplo de creación de factura básica con la nueva configuración

require_once __DIR__ . '/../vendor/autoload.php';

use josemmo\Verifactu\Models\Records\RegistrationRecord;
use josemmo\Verifactu\Models\Records\InvoiceIdentifier;
use josemmo\Verifactu\Models\Records\FiscalIdentifier;
use josemmo\Verifactu\Models\Records\BreakdownDetails;
use josemmo\Verifactu\Models\Records\InvoiceType;
use josemmo\Verifactu\Models\Records\OperationType;

try {
    // Cargar configuración
    $configData = require __DIR__ . '/../config/verifactu_simple.php';
    $client = $configData['client'];
    $system = $configData['system'];
    $config = $configData['config'];

    echo "=== CREANDO PRIMERA FACTURA VERIFACTU ===\n\n";

    // Crear registro de factura
    $record = new RegistrationRecord();

    // 1. Identificador de factura (API correcta)
    $numeroFactura = 'FAC-TEST-' . date('Ymd-His');
    $invoiceId = new InvoiceIdentifier(
        $config['empresa']['nif'],  // issuerId
        $numeroFactura,             // invoiceNumber  
        new DateTimeImmutable()     // issueDate (fecha actual)
    );
    $record->setInvoiceIdentifier($invoiceId);

    echo "📋 Número factura: $numeroFactura\n";
    echo "📅 Fecha: " . date('Y-m-d') . "\n";

    // 2. Emisor (tu empresa)
    $issuer = new FiscalIdentifier($config['empresa']['nombre'], $config['empresa']['nif']);
    $record->setIssuer($issuer);

    echo "🏢 Emisor: {$config['empresa']['nombre']} ({$config['empresa']['nif']})\n";

    // 3. Cliente (ejemplo)
    $recipient = new FiscalIdentifier('Cliente Ejemplo SL', 'B87654321');
    $record->setRecipient($recipient);

    echo "👤 Cliente: Cliente Ejemplo SL (B87654321)\n";

    // 4. Tipo de factura
    $record->setInvoiceType(InvoiceType::F1); // Factura completa
    $record->setOperationType(OperationType::A0); // Operación ordinaria

    echo "📄 Tipo: Factura completa (F1)\n";

    // 5. Líneas de factura
    $breakdown = new BreakdownDetails();
    $breakdown->setTaxableBase(100.00);
    $breakdown->setTaxRate(21.00);
    $breakdown->setTaxAmount(21.00);
    $record->addBreakdownDetail($breakdown);

    $record->setInvoiceTotal(121.00);

    echo "💰 Base: 100.00 € + IVA (21%): 21.00 € = Total: 121.00 €\n\n";

    // 6. Sistema informático
    $record->setComputerSystem($system);

    // 7. Enviar a AEAT
    echo "📤 Enviando a AEAT (sandbox)...\n";

    $response = $client->submitRecord($record);

    if ($response->isAccepted()) {
        echo "\n🎉 ¡FACTURA ENVIADA CORRECTAMENTE!\n\n";
        echo "✅ Estado: Aceptada\n";
        echo "🆔 CSV: " . $response->getCsv() . "\n";
        echo "📱 QR: " . substr($response->getQrCode(), 0, 50) . "...\n";

        // Guardar datos
        $facturaData = [
            'numero' => $numeroFactura,
            'fecha' => date('Y-m-d H:i:s'),
            'total' => 121.00,
            'csv' => $response->getCsv(),
            'qr' => $response->getQrCode()
        ];

        file_put_contents(__DIR__ . '/../logs/primera-factura.json', json_encode($facturaData, JSON_PRETTY_PRINT));
        echo "💾 Datos guardados en logs/primera-factura.json\n";
    } else {
        echo "\n❌ FACTURA RECHAZADA\n\n";
        foreach ($response->getErrors() as $error) {
            echo "- $error\n";
        }
    }
} catch (Exception $e) {
    echo "\n💥 ERROR: " . $e->getMessage() . "\n";
    echo "📁 Archivo: " . $e->getFile() . " (línea " . $e->getLine() . ")\n";
}

echo "\n=== FIN ===\n";
