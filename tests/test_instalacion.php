<?php
echo "=== TEST BÁSICO POST-INSTALACIÓN ===\n\n";

$errors = 0;

// Test 1: Verificar estructura
echo "1. Verificando estructura de directorios...\n";
$dirs = ["config", "certificates", "logs", "tests", "examples", "src"];
foreach ($dirs as $dir) {
    if (is_dir($dir)) {
        echo "   ✅ {$dir}/\n";
    } else {
        echo "   ❌ {$dir}/ - FALTANTE\n";
        $errors++;
    }
}

// Test 2: Verificar archivos base
echo "\n2. Verificando archivos base...\n";
$files = ["composer.json", ".env.example", ".gitignore", "README.md"];
foreach ($files as $file) {
    if (file_exists($file)) {
        echo "   ✅ {$file}\n";
    } else {
        echo "   ❌ {$file} - FALTANTE\n";
        $errors++;
    }
}

// Test 3: Verificar permisos de certificates
echo "\n3. Verificando permisos...\n";
$perms = fileperms("certificates") & 0777;
if ($perms === 0700) {
    echo "   ✅ certificates/ tiene permisos correctos (700)\n";
} else {
    echo "   ⚠️  certificates/ tiene permisos " . sprintf("%04o", $perms) . " (recomendado: 0700)\n";
}

// Test 4: Verificar Composer
echo "\n4. Verificando Composer...\n";
if (file_exists("vendor/autoload.php")) {
    echo "   ✅ Dependencias instaladas\n";
    require_once "vendor/autoload.php";
    
    if (class_exists("josemmo\\Verifactu\\Services\\AeatClient")) {
        echo "   ✅ Librería VeriFactu disponible\n";
    } else {
        echo "   ❌ Librería VeriFactu no encontrada\n";
        $errors++;
    }
} else {
    echo "   ⚠️  Dependencias no instaladas. Ejecutar: composer install\n";
}

// Test 5: Estado de configuración
echo "\n5. Estado de configuración...\n";
if (file_exists(".env")) {
    echo "   ✅ Archivo .env configurado\n";
} else {
    echo "   ⚠️  Archivo .env no encontrado. Copiar desde .env.example\n";
}

if (file_exists("certificates/fnmt-test.pfx")) {
    echo "   ✅ Certificado de desarrollo encontrado\n";
} else {
    echo "   ⏳ Certificado pendiente de obtener desde FNMT\n";
}

// Resumen
echo "\n=== RESUMEN ===\n";
if ($errors === 0) {
    echo "🎉 Instalación base completada correctamente\n";
    echo "\n📋 Próximos pasos:\n";
    echo "1. cp .env.example .env\n";
    echo "2. Editar .env con datos reales\n";
    echo "3. Obtener certificado FNMT\n";
    echo "4. Ejecutar tests completos\n";
} else {
    echo "⚠️  Se encontraron {$errors} problemas\n";
    echo "Revisar la instalación antes de continuar\n";
}

echo "\n=== FIN TEST ===\n";
exit($errors > 0 ? 1 : 0);
