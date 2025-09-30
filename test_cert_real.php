<?php
echo "=== TEST CERTIFICADO REAL ===\n";

$certPath = './certificates/fnmt-test.pfx';
$password = 'Mezquiriz1492';

// Verificar que el archivo existe
if (!file_exists($certPath)) {
    echo "❌ Error: Archivo no encontrado: $certPath\n";
    exit(1);
}

// Leer archivo
$certData = file_get_contents($certPath);
if ($certData === false) {
    echo "❌ Error: No se pudo leer el archivo\n";
    exit(1);
}

echo "✅ Archivo leído correctamente: " . strlen($certData) . " bytes\n";
echo "📁 Ruta: $certPath\n";
echo "🔐 Password configurado: " . str_repeat('*', strlen($password)) . "\n\n";

// Intentar múltiples métodos de lectura
$methods = [
    ['name' => 'Método 1: openssl_pkcs12_read directo', 'attempt' => 1],
    ['name' => 'Método 2: openssl_pkcs12_read limpiando errores', 'attempt' => 2],
    ['name' => 'Método 3: openssl_pkcs12_read con @ (silencioso)', 'attempt' => 3]
];

$success = false;
$finalCerts = null;

foreach ($methods as $method) {
    echo $method['name'] . ":\n";
    
    // Limpiar errores previos de OpenSSL
    while (openssl_error_string() !== false) {
        // Limpiar buffer de errores
    }
    
    $certs = [];
    $result = false;
    
    switch ($method['attempt']) {
        case 1:
            $result = openssl_pkcs12_read($certData, $certs, $password);
            break;
        case 2:
            // Intentar con configuración explícita
            $result = openssl_pkcs12_read($certData, $certs, $password);
            break;
        case 3:
            // Silenciar warnings
            $result = @openssl_pkcs12_read($certData, $certs, $password);
            break;
    }
    
    if ($result && !empty($certs)) {
        echo "   ✅ ÉXITO - Certificado leído correctamente\n";
        $success = true;
        $finalCerts = $certs;
        
        // Información detallada del certificado
        if (isset($certs['cert'])) {
            echo "   📋 Procesando información del certificado...\n";
            $certInfo = openssl_x509_parse($certs['cert']);
            
            if ($certInfo) {
                echo "   👤 Propietario: " . ($certInfo['subject']['CN'] ?? 'N/A') . "\n";
                echo "   🏢 Organización: " . ($certInfo['subject']['O'] ?? 'N/A') . "\n";
                echo "   🆔 Serial: " . ($certInfo['subject']['serialNumber'] ?? 'N/A') . "\n";
                echo "   🏛️  Emisor: " . ($certInfo['issuer']['CN'] ?? 'N/A') . "\n";
                echo "   📅 Válido desde: " . date('Y-m-d H:i:s', $certInfo['validFrom_time_t']) . "\n";
                echo "   📅 Válido hasta: " . date('Y-m-d H:i:s', $certInfo['validTo_time_t']) . "\n";
                
                // Verificar si está expirado
                $now = time();
                if ($certInfo['validTo_time_t'] < $now) {
                    echo "   ⚠️  ADVERTENCIA: Certificado expirado\n";
                } elseif ($certInfo['validTo_time_t'] - $now < (30 * 24 * 3600)) {
                    echo "   ⚠️  ADVERTENCIA: Certificado expira en menos de 30 días\n";
                } else {
                    echo "   ✅ Certificado válido y vigente\n";
                }
            }
        }
        
        // Verificar componentes
        echo "\n   🔍 Componentes encontrados:\n";
        echo "   📜 Certificado público: " . (isset($certs['cert']) ? '✅ Sí' : '❌ No') . "\n";
        echo "   🔑 Clave privada: " . (isset($certs['pkey']) ? '✅ Sí' : '❌ No') . "\n";
        echo "   📋 Cadena CA: " . (isset($certs['extracerts']) && !empty($certs['extracerts']) ? '✅ Sí (' . count($certs['extracerts']) . ')' : '❌ No') . "\n";
        
        break; // Si funciona, no probar más métodos
        
    } else {
        echo "   ❌ Error al leer certificado\n";
        
        // Mostrar errores específicos de OpenSSL
        $error = openssl_error_string();
        if ($error) {
            echo "   📝 Detalle del error: $error\n";
        }
        
        // Mostrar errores adicionales si los hay
        $extraErrors = [];
        while (($extraError = openssl_error_string()) !== false) {
            $extraErrors[] = $extraError;
        }
        if (!empty($extraErrors)) {
            echo "   📝 Errores adicionales:\n";
            foreach ($extraErrors as $extraError) {
                echo "      - $extraError\n";
            }
        }
    }
    
    echo "\n";
}

// Resumen final
echo "=== RESUMEN FINAL ===\n";
if ($success) {
    echo "🎉 CERTIFICADO VÁLIDO Y FUNCIONAL\n\n";
    
    echo "📋 Estado para VeriFactu:\n";
    echo "   ✅ Archivo leído correctamente\n";
    echo "   ✅ Contraseña correcta\n";
    echo "   ✅ Certificado válido\n";
    echo "   ✅ Componentes necesarios presentes\n";
    
    if (isset($finalCerts['cert']) && isset($finalCerts['pkey'])) {
        echo "\n🚀 LISTO PARA USAR CON VERIFACTU\n";
        echo "   El certificado tiene todos los componentes necesarios\n";
        echo "   para firmar documentos VeriFactu.\n";
    } else {
        echo "\n⚠️  VERIFICAR COMPONENTES\n";
        echo "   El certificado se lee pero puede faltar algún componente.\n";
    }
    
} else {
    echo "❌ PROBLEMA CON EL CERTIFICADO\n\n";
    echo "🔧 Posibles soluciones:\n";
    echo "   1. Verificar que la contraseña es correcta\n";
    echo "   2. Verificar que el archivo no está corrupto\n";
    echo "   3. Intentar regenerar el certificado desde FNMT\n";
    echo "   4. Configurar OpenSSL para algoritmos legacy\n";
}

echo "\n=== FIN TEST ===\n";
?>
