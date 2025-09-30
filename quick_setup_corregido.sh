#!/bin/bash
# Script de configuración rápida para desarrollo (VERSIÓN CORREGIDA)

echo "⚡ CONFIGURACIÓN RÁPIDA VERIFACTU"
echo "==============================="
echo ""

# Verificar que estamos en el directorio correcto
if [[ ! -f composer.json ]]; then
    echo "❌ ERROR: Ejecutar desde el directorio del proyecto VeriFactu"
    exit 1
fi

echo "📝 Configuración rápida de variables de entorno"
echo ""

# Función corregida para input del usuario
read_input() {
    local prompt="$1"
    local value
    echo -n "$prompt: "
    read -r value
    echo "$value"
}

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

# Mostrar resumen
echo "📋 RESUMEN DE CONFIGURACIÓN:"
echo "   🏢 Empresa: $EMPRESA_NOMBRE ($EMPRESA_NIF)"
echo "   👨‍💻 Desarrollador: $DESARROLLADOR_NOMBRE ($DESARROLLADOR_NIF)"
echo "   🔐 Certificado configurado: ✅"
echo ""

echo "🎉 Configuración completada"
echo ""
echo "📋 Próximos pasos:"
echo "   1. Obtener certificado FNMT (si no lo tienes)"
echo "      → ./info_certificados.sh"
echo "   2. Colocar certificado en: certificates/fnmt-test.pfx"
echo "   3. Verificar configuración:"
echo "      → php tests/test_instalacion.php"
echo ""
echo "💡 Para ver ayuda completa: ./ayuda_rapida.sh"

