#!/bin/bash
# Script para solucionar problemas de Composer en VeriFactu

echo "🔧 SOLUCIONADOR DE PROBLEMAS COMPOSER"
echo "====================================="
echo ""

# Verificar que estamos en el directorio correcto
if [[ ! -f composer.json ]]; then
    echo "❌ ERROR: Ejecutar desde el directorio mi-proyecto-verifactu"
    exit 1
fi

echo "📋 Diagnóstico del problema..."
echo ""

# Paso 1: Limpiar cache de Composer
echo "1️⃣  Limpiando cache de Composer..."
composer clear-cache
echo "✅ Cache limpiado"
echo ""

# Paso 2: Verificar versión de la librería disponible
echo "2️⃣  Verificando versiones disponibles de josemmo/verifactu-php..."
composer show josemmo/verifactu-php --available 2>/dev/null || echo "   ⚠️  Librería no encontrada en repositorios"
echo ""

# Paso 3: Crear un composer.json más permisivo
echo "3️⃣  Creando composer.json más flexible..."

cat > composer.json << 'COMPOSER_EOF'
{
    "name": "mi-empresa/verifactu-project",
    "description": "Proyecto de implementación VeriFactu con PHP",
    "type": "project",
    "authors": [
        {
            "name": "Desarrollador VeriFactu",
            "email": "developer@example.com"
        }
    ],
    "require": {
        "php": ">=7.4",
        "josemmo/verifactu-php": "*"
    },
    "autoload": {
        "psr-4": {
            "App\\": "src/"
        }
    },
    "config": {
        "optimize-autoloader": true,
        "sort-packages": true
    },
    "minimum-stability": "dev",
    "prefer-stable": true
}
COMPOSER_EOF

echo "✅ composer.json actualizado con versiones más flexibles"
echo ""

# Paso 4: Intentar instalación con diferentes estrategias
echo "4️⃣  Intentando instalación..."

# Estrategia 1: Instalación básica
echo "   Estrategia 1: Instalación estándar..."
if XDEBUG_MODE=off composer install --no-dev --optimize-autoloader 2>/dev/null; then
    echo "   ✅ Éxito con instalación estándar"
    exit 0
fi

# Estrategia 2: Actualizar repositorios y reinstalar
echo "   Estrategia 2: Actualizar repositorios..."
if XDEBUG_MODE=off composer update --no-dev --optimize-autoloader 2>/dev/null; then
    echo "   ✅ Éxito con actualización"
    exit 0
fi

# Estrategia 3: Instalación con --ignore-platform-reqs
echo "   Estrategia 3: Ignorar requisitos de plataforma..."
if XDEBUG_MODE=off composer install --no-dev --optimize-autoloader --ignore-platform-reqs 2>/dev/null; then
    echo "   ✅ Éxito ignorando requisitos de plataforma"
    echo "   ⚠️  Nota: Se ignoraron algunos requisitos de plataforma"
    exit 0
fi

# Estrategia 4: Instalación manual de la librería específica
echo "   Estrategia 4: Instalación manual de josemmo/verifactu-php..."
if XDEBUG_MODE=off composer require josemmo/verifactu-php --ignore-platform-reqs 2>/dev/null; then
    echo "   ✅ Éxito con instalación manual"
    exit 0
fi

# Estrategia 5: Crear versión sin la librería externa (solo estructura)
echo "   Estrategia 5: Crear proyecto básico sin librería externa..."

cat > composer.json << 'BASIC_COMPOSER_EOF'
{
    "name": "mi-empresa/verifactu-project",
    "description": "Proyecto de implementación VeriFactu con PHP - Versión básica",
    "type": "project",
    "authors": [
        {
            "name": "Desarrollador VeriFactu",
            "email": "developer@example.com"
        }
    ],
    "require": {
        "php": ">=7.4"
    },
    "autoload": {
        "psr-4": {
            "App\\": "src/"
        }
    }
}
BASIC_COMPOSER_EOF

if XDEBUG_MODE=off composer install 2>/dev/null; then
    echo "   ✅ Proyecto básico creado (sin librería VeriFactu)"
    echo ""
    echo "   📋 PRÓXIMOS PASOS MANUALES:"
    echo "   1. Instalar la librería manualmente:"
    echo "      composer require josemmo/verifactu-php"
    echo "   2. O descargar manualmente desde GitHub"
    echo "   3. Verificar requisitos de sistema específicos"
    echo ""
    exit 0
fi

# Si llegamos aquí, nada funcionó
echo "❌ No se pudo resolver el problema automáticamente"
echo ""
echo "🔍 INFORMACIÓN DE DEPURACIÓN:"
echo ""

echo "   📊 Versión PHP:"
php --version | head -1

echo ""
echo "   📊 Versión Composer:"
composer --version

echo ""
echo "   📊 Configuración Composer:"
composer config --list | grep -E "(repo|platform|minimum-stability)"

echo ""
echo "   📊 Último error detallado:"
XDEBUG_MODE=off composer install --no-dev -vvv

echo ""
echo "🛠️  SOLUCIONES MANUALES:"
echo ""
echo "1️⃣  Verificar conectividad:"
echo "   curl -I https://packagist.org"
echo ""
echo "2️⃣  Limpiar completamente Composer:"
echo "   rm -rf vendor/ composer.lock"
echo "   composer clear-cache"
echo "   composer install"
echo ""
echo "3️⃣  Verificar que josemmo/verifactu-php existe:"
echo "   composer search josemmo/verifactu"
echo ""
echo "4️⃣  Instalar version específica:"
echo "   composer require 'josemmo/verifactu-php:^1.0'"
echo ""
echo "5️⃣  Si la librería no existe, usar alternativa:"
echo "   composer require 'eseperio/verifactu-php'"
echo ""

exit 1
