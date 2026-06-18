# Documentacion de Jobs

Documento generado a partir de la lectura de las clases en `Modules/Intercom/app/Jobs`, del scheduler en `Modules/Intercom/routes/console.php` y de la infraestructura definida en `docker-compose.yml`.

## Resumen

Jobs reales detectados en el proyecto:

- `ProcesarPhytosanitaryCertificateMBA002`
- `PermitePhytosanitaryImportMBA002`

Ambos:

- implementan `ShouldQueue`
- se ejecutan desde el scheduler
- dependen de `queue:work` para ser procesados
- usan timezone `America/Guayaquil`
- tienen `withoutOverlapping()`
- tienen `onOneServer()`

## Ubicacion

Clases:

- [ProcesarPhytosanitaryCertificateMBA002.php](/home/cyan/Develop/interoperabilidad_intercom/Modules/Intercom/app/Jobs/ProcesarPhytosanitaryCertificateMBA002.php)
- [PermitePhytosanitaryImportMBA002.php](/home/cyan/Develop/interoperabilidad_intercom/Modules/Intercom/app/Jobs/PermitePhytosanitaryImportMBA002.php)

Programacion:

- [Modules/Intercom/routes/console.php](/home/cyan/Develop/interoperabilidad_intercom/Modules/Intercom/routes/console.php)

Infraestructura:

- [docker-compose.yml](/home/cyan/Develop/interoperabilidad_intercom/docker-compose.yml)

## Scheduler

El modulo registra sus jobs asi:

```php
$schedule->job(new ProcesarPhytosanitaryCertificateMBA002)
    ->everyMinute()
    ->timezone('America/Guayaquil')
    ->withoutOverlapping()
    ->onOneServer();

$schedule->job(new PermitePhytosanitaryImportMBA002)
    ->everyTenMinutes()
    ->timezone('America/Guayaquil')
    ->withoutOverlapping()
    ->onOneServer();
```

Interpretacion:

- `everyMinute()`: el job de CFE se agenda cada minuto
- `everyTenMinutes()`: el job de PFI se agenda cada diez minutos
- `withoutOverlapping()`: evita que se solapen ejecuciones del mismo job
- `onOneServer()`: pensado para despliegues con multiples instancias

## Infraestructura de ejecucion

En `docker-compose.yml` hay dos procesos relevantes:

- contenedor `queue`
  - ejecuta `php artisan queue:work --queue=default --sleep=3 --tries=3 --timeout=${LARAVEL_QUEUE_TIMEOUT:-90}`
- contenedor `scheduler`
  - ejecuta `php artisan schedule:work`

Esto significa:

- el scheduler agenda los jobs
- el worker de cola los procesa

## Job: ProcesarPhytosanitaryCertificateMBA002

Archivo:

- [ProcesarPhytosanitaryCertificateMBA002.php](/home/cyan/Develop/interoperabilidad_intercom/Modules/Intercom/app/Jobs/ProcesarPhytosanitaryCertificateMBA002.php)

Frecuencia:

- cada minuto

Objetivo:

- buscar certificados fitosanitarios de exportacion CFE pendientes en Agrocalidad
- completar los productos asociados a cada certificado
- enviar cada certificado a Intercom por el flujo MBA002

Flujo interno:

1. instancia `AgrocalidadCFEDataService`
2. ejecuta `fetchAllExportPhytosanitaryCertificatesData()`
3. recorre cada certificado encontrado
4. consulta productos con `fetchCertificateProductsData($id_certificado_fitosanitario)`
5. agrega esos productos al objeto de certificado
6. instancia `IntercomCfeService`
7. envía con `sendExportPhytosanitaryCertificateMba002($cetificado)`

Dependencias principales:

- `Modules\Intercom\Services\AgrocalidadCFEDataService`
- `Modules\Intercom\Repositories\AgrocalidadDBCFERepository`
- `Modules\Intercom\Services\IntercomCfeService`

Manejo de errores:

- captura excepciones y escribe log con:
  - `Ocurrio el error class ProcesarPhytosanitaryCertificateMBA002: ...`

## Job: PermitePhytosanitaryImportMBA002

Archivo:

- [PermitePhytosanitaryImportMBA002.php](/home/cyan/Develop/interoperabilidad_intercom/Modules/Intercom/app/Jobs/PermitePhytosanitaryImportMBA002.php)

Frecuencia:

- cada 10 minutos

Objetivo:

- buscar permisos fitosanitarios de importacion PFI pendientes en Agrocalidad
- completar los productos asociados a cada permiso
- enviar cada permiso a Intercom por el flujo MBA002

Flujo interno:

1. instancia `AgrocalidadPFIDataService`
2. ejecuta `fetchAllPhytosanitaryImportPermitData()`
3. recorre cada permiso encontrado
4. consulta productos con `fetchAllPhytosanitaryImportPermitProductos($permiso->id_importacion)`
5. agrega esos productos al objeto de permiso
6. instancia `IntercomPfiService`
7. envía con `sendPhytonsanitaryImportPermitMba002($permiso)`

Dependencias principales:

- `Modules\Intercom\Services\AgrocalidadPFIDataService`
- `Modules\Intercom\Repositories\AgrocalidadDBPFIRepository`
- `Modules\Intercom\Services\IntercomPfiService`

Manejo de errores:

- captura excepciones y escribe log con:
  - `Error class PermitePhytosanitaryImportMBA002: ...`

## Observaciones tecnicas

- No se encontraron otros jobs reales aparte de estos dos.
- Los archivos encontrados en `stubs/` no son jobs activos; son plantillas del generador de modulos.
- Ambos jobs instancian servicios manualmente dentro de `handle()` en lugar de usar inyeccion por constructor.
- Ambos jobs recorren lotes completos y envian uno por uno; si el volumen crece, este diseno puede impactar el tiempo de ejecucion.

## Referencias utiles

Inventario rapido de jobs:

```bash
rg --files . | rg "Jobs|Job"
```

Busqueda de jobs y scheduler:

```bash
rg -n "ShouldQueue|schedule:|Schedule::|dispatch\\(" .
```

Ver scheduler del modulo:

```bash
sed -n '1,240p' Modules/Intercom/routes/console.php
```
