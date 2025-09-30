#!/bin/bash
# Script para completar la instalación de VeriFactu

echo "🔧 COMPLETANDO INSTALACIÓN VERIFACTU"
echo "==================================="
echo ""

# Verificar que estamos en el directorio correcto
if [[ ! -f composer.json ]] || [[ ! -d vendor ]]; then
    echo "❌ ERROR: Ejecutar desde el directorio mi-proyecto-verifactu"
    echo "   Debe contener composer.json y vendor/"
    exit 1
fi

echo "✅ Directorio correcto detectado"
echo "📁 Ubicación: $(pwd)"
echo ""

# Crear test de instalación
echo "1️⃣  Creando test de instalación..."
cat > tests/test_instalacion.php << 'TEST_EOF'
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
TEST_EOF
echo "✅ tests/test_instalacion.php creado"

# Crear .env.example
echo ""
echo "2️⃣  Creando .env.example..."
cat > .env.example << 'ENV_EOF'
# Configuración VeriFactu - Generado automáticamente
# Renombrar a .env y configurar con datos reales

VERIFACTU_ENV=development
VERIFACTU_CERT_PATH=./certificates/fnmt-test.pfx
VERIFACTU_CERT_PASSWORD=tu-password-segura-aqui
VERIFACTU_DEBUG=true
VERIFACTU_LOG_LEVEL=debug

# Datos de tu empresa (OBLIGATORIO CAMBIAR)
EMPRESA_NIF=A12345678
EMPRESA_NOMBRE=Mi Empresa SL
DESARROLLADOR_NIF=A12345678
DESARROLLADOR_NOMBRE=Tu Nombre

# URLs AEAT (no cambiar)
AEAT_SANDBOX_URL=https://prewww2.aeat.es
AEAT_PRODUCTION_URL=https://www2.agenciatributaria.gob.es
ENV_EOF
echo "✅ .env.example creado"

# Crear .gitignore
echo ""
echo "3️⃣  Creando .gitignore..."
cat > .gitignore << 'GITIGNORE_EOF'
# Dependencias
vendor/
composer.lock

# Configuración local
.env

# Certificados (CRÍTICO: nunca subir al repositorio)
certificates/*.pfx
certificates/*.p12
certificates/*.crt
certificates/*.key

# Logs
logs/*.log
logs/*.txt

# IDE y editores
.vscode/
.idea/
*.swp
*.swo

# Sistema operativo
.DS_Store
Thumbs.db

# Temporales
tmp/
temp/
*.tmp
*.cache
GITIGNORE_EOF
echo "✅ .gitignore creado"

# Crear README.md
echo ""
echo "4️⃣  Creando README.md..."
cat > README.md << 'README_EOF'
# Proyecto VeriFactu PHP

Proyecto generado automáticamente el $(date)

## Estado de la instalación
- ✅ Estructura de directorios
- ✅ Configuración base
- ✅ Dependencias instaladas
- ⏳ Pendiente: configurar .env
- ⏳ Pendiente: obtener certificado FNMT

## Próximos pasos

### 1. Configurar entorno
```bash
cp .env.example .env
# Editar .env con tus datos reales
```

### 2. Obtener certificado FNMT
1. Solicitar en: https://www.sede.fnmt.gob.es/certificados/persona-fisica
2. Guardar como: `certificates/fnmt-test.pfx`
3. Actualizar contraseña en `.env`

### 3. Ejecutar tests
```bash
php tests/test_instalacion.php  # Test básico
```

## Estructura del proyecto

```
mi-proyecto-verifactu/
├── config/           # Configuración del sistema
├── certificates/     # Certificados digitales (vacío inicialmente)
├── logs/            # Logs del sistema
├── tests/           # Tests de verificación
├── examples/        # Ejemplos de uso
├── src/             # Código de la aplicación
├── vendor/          # Librerías Composer (instaladas)
├── .env.example     # Plantilla de configuración
└── composer.json    # Dependencias
```

## Seguridad

🔒 **IMPORTANTE**: El directorio `certificates/` tiene permisos restrictivos (700) y está excluido del control de versiones.

## Documentación

- 📚 Informe completo en documento "Informe VF"
- 🔗 [Librería josemmo/verifactu-php](https://packagist.org/packages/josemmo/verifactu-php)
- 🏛️ [Documentación oficial VeriFactu](https://sede.agenciatributaria.gob.es/Sede/iva/sistemas-informaticos-facturacion-verifactu.html)

---
*Generado automáticamente por el instalador VeriFactu PHP*
README_EOF
echo "✅ README.md creado"

# Crear scripts de ayuda
echo ""
echo "5️⃣  Creando scripts de ayuda..."

# quick_setup.sh
cat > quick_setup.sh << 'SETUP_EOF'
#!/bin/bash
# Script de configuración rápida para desarrollo

echo "⚡ CONFIGURACIÓN RÁPIDA VERIFACTU"
echo "==============================="
echo ""

# Verificar que estamos en el directorio correcto
if [[ ! -f composer.json ]]; then
    echo "❌ ERROR: Ejecutar desde el directorio del proyecto VeriFactu"
    exit 1
fi

# Función para input del usuario
read_input() {
    echo -n "$1: "
    read value
    echo "$value"
}

echo "📝 Configuración rápida de variables de entorno"
echo ""

# Leer datos de la empresa
echo "🏢 DATOS DE TU EMPRESA:"
EMPRESA_NIF=$(read_input "NIF de la empresa (ej: A12345678)")
EMPRESA_NOMBRE=$(read_input "Nombre de la empresa")

echo ""
echo "👨‍💻 DATOS DEL DESARROLLADOR:"
DESARROLLADOR_NIF=$(read_input "NIF del desarrollador (ej: 12345678Z)")
DESARROLLADOR_NOMBRE=$(read_input "Nombre del desarrollador")

echo ""
echo "🔐 CERTIFICADO:"
CERT_PASSWORD=$(read_input "Contraseña del certificado FNMT")

echo ""
echo "📝 Generando archivo .env..."

# Crear .env personalizado
cat > .env << ENV_EOF
# Configuración VeriFactu - Generado automáticamente
# Fecha: $(date)

# Entorno de desarrollo
VERIFACTU_ENV=development
VERIFACTU_CERT_PATH=./certificates/fnmt-test.pfx
VERIFACTU_CERT_PASSWORD=$CERT_PASSWORD
VERIFACTU_DEBUG=true
VERIFACTU_LOG_LEVEL=debug

# Datos de la empresa
EMPRESA_NIF=$EMPRESA_NIF
EMPRESA_NOMBRE=$EMPRESA_NOMBRE

# Datos del desarrollador
DESARROLLADOR_NIF=$DESARROLLADOR_NIF
DESARROLLADOR_NOMBRE=$DESARROLLADOR_NOMBRE

# URLs AEAT (no cambiar)
AEAT_SANDBOX_URL=https://prewww2.aeat.es
AEAT_PRODUCTION_URL=https://www2.agenciatributaria.gob.es
ENV_EOF

echo "✅ Archivo .env configurado"
echo ""
echo "🎉 Configuración completada"
echo ""
echo "📋 Próximos pasos:"
echo "   1. Obtener certificado FNMT (si no lo tienes)"
echo "   2. php tests/test_instalacion.php"
echo "   3. Colocar certificado en certificates/fnmt-test.pfx"
echo ""
SETUP_EOF

chmod +x quick_setup.sh
echo "✅ quick_setup.sh creado"

# ayuda_rapida.sh
cat > ayuda_rapida.sh << 'HELP_EOF'
#!/bin/bash
# Ayuda rápida para el proyecto VeriFactu

echo "🚀 AYUDA RÁPIDA - PROYECTO VERIFACTU"
echo "=================================="
echo ""
echo "📁 Ubicación actual: $(pwd)"
echo ""
echo "🔧 COMANDOS ÚTILES:"
echo ""
echo "   📊 Tests de verificación:"
echo "      php tests/test_instalacion.php"
echo ""
echo "   🔧 Configuración:"
echo "      ./quick_setup.sh               # Configuración interactiva"
echo "      nano .env                      # Editar configuración manual"
echo ""
echo "   🔧 Mantenimiento:"
echo "      composer install               # Reinstalar dependencias"
echo "      composer update                # Actualizar dependencias"
echo "      rm logs/*.log                  # Limpiar logs"
echo ""
echo "📋 ARCHIVOS IMPORTANTES:"
echo "   .env                            # Configuración (EDITAR)"
echo "   certificates/                   # Certificados (OBTENER FNMT)"
echo "   logs/                           # Logs del sistema"
echo ""
echo "🆘 PROBLEMAS FRECUENTES:"
echo ""
echo "   ❌ Necesito certificado FNMT:"
echo "      → https://www.sede.fnmt.gob.es/certificados/persona-fisica"
echo ""
echo "   ❌ 'Class not found':"
echo "      → Ejecutar: composer install"
echo ""
echo "📚 Más información: README.md"
echo ""
HELP_EOF

chmod +x ayuda_rapida.sh
echo "✅ ayuda_rapida.sh creado"

# info_certificados.sh
cat > info_certificados.sh << 'CERT_INFO_EOF'
#!/bin/bash
# Información completa sobre certificados FNMT para VeriFactu

echo "🔐 GUÍA COMPLETA: CERTIFICADOS FNMT PARA VERIFACTU"
echo "=============================================="
echo ""

echo "📋 PROCESO COMPLETO PASO A PASO:"
echo ""

echo "1️⃣  SOLICITUD ONLINE (5 minutos)"
echo "   🌐 URL: https://www.sede.fnmt.gob.es/certificados/persona-fisica"
echo "   📝 Datos necesarios:"
echo "      - DNI/NIE válido"
echo "      - Email activo"
echo "      - Teléfono móvil"
echo "   ⚠️  IMPORTANTE: No cerrar el navegador hasta completar todo"
echo ""

echo "2️⃣  ACREDITACIÓN PRESENCIAL (15 minutos)"
echo "   📄 Documentos a llevar:"
echo "      - DNI/NIE original (no vale copia)"
echo "      - Código de solicitud (del email)"
echo "   🏢 Lugares disponibles:"
echo "      - Oficinas FNMT"
echo "      - Oficinas de Correos"
echo "      - Algunas administraciones locales"
echo ""

echo "3️⃣  DESCARGA DEL CERTIFICADO (2-3 días después)"
echo "   ⏰ Plazo: 24-72 horas después de la acreditación"
echo "   💻 CRÍTICO: Usar el MISMO navegador de la solicitud inicial"
echo "   🔑 Crear contraseña SEGURA para el certificado"
echo "   💾 Guardar archivo .pfx en: certificates/fnmt-test.pfx"
echo ""

echo "💡 CONSEJOS DE SEGURIDAD:"
echo "   🔒 Usar contraseña fuerte (mín 12 caracteres)"
echo "   💾 Hacer backup del certificado"
echo "   📅 Anotar fecha de caducidad (4 años)"
echo "   🚫 NUNCA subir certificado a repositorios públicos"
echo ""

echo "🧪 TESTING EN VERIFACTU:"
echo "   Una vez obtenido el certificado:"
echo "   1. Guardarlo como: certificates/fnmt-test.pfx"
echo "   2. Actualizar .env con la contraseña"
echo "   3. Ejecutar: php tests/test_instalacion.php"
echo ""

echo "📞 CONTACTO FNMT:"
echo "   📧 Email: info@fnmt.es"
echo "   📱 Teléfono: 902 363 467"
echo "   🕐 Horario: L-V 8:30-15:00h"
echo ""
CERT_INFO_EOF

chmod +x info_certificados.sh
echo "✅ info_certificados.sh creado"

# Copiar .env
echo ""
echo "6️⃣  Configuración inicial..."
if [[ ! -f .env ]]; then
    cp .env.example .env
    echo "✅ Archivo .env creado desde plantilla"
    echo "⚠️  IMPORTANTE: Edita el archivo .env con tus datos reales"
else
    echo "ℹ️  Archivo .env ya existe"
fi

# Configurar permisos finales
chmod 700 certificates/
chmod 755 logs/ tests/ examples/ src/ config/

echo ""
echo "🎉 ¡INSTALACIÓN COMPLETADA!"
echo ""
echo "📋 ARCHIVOS CREADOS:"
echo "   ✅ tests/test_instalacion.php"
echo "   ✅ .env.example"
echo "   ✅ .gitignore"
echo "   ✅ README.md"
echo "   ✅ quick_setup.sh"
echo "   ✅ ayuda_rapida.sh"
echo "   ✅ info_certificados.sh"
echo "   ✅ .env (desde plantilla)"
echo ""
echo "📋 PRÓXIMOS PASOS:"
echo "   1. php tests/test_instalacion.php    # Verificar instalación"
echo "   2. ./quick_setup.sh                  # Configurar variables"
echo "   3. ./info_certificados.sh            # Obtener certificado FNMT"
echo ""
echo "🎯 ¡Todo listo para empezar con VeriFactu!"
