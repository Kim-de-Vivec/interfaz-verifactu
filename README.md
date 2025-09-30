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
