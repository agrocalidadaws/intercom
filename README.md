# INTERCOM-AGROCALIDAD

Este repositorio es, en la práctica, el contenedor de ejecución del módulo [Modules/Intercom](/home/cyan/Develop/interoperabilidad_intercom/Modules/Intercom:1). La mayor parte del valor funcional vive ahí: autenticación API, integración con Intercom/CAN, lectura desde Agrocalidad, generación y validación XML, persistencia local de documentos y procesamiento asíncrono de CFE y PFI.

## Qué resuelve el módulo `Intercom`

`Modules/Intercom` implementa interoperabilidad fitosanitaria para dos dominios:

- `CFE`: Certificados Fitosanitarios de Exportación.
- `PFI`: Permisos Fitosanitarios de Importación.

Sobre esos dominios el módulo cubre:

- autenticación OAuth para consumidores de la API;
- envío de documentos `MBA002`;
- consulta de estado `MBA005`;
- solicitud de envío o reenvío `MBA011`;
- recepción de documentos `MEX501`;
- notificaciones y estados `MEX502` y `MEX503`;
- descarga de XML y PDF almacenados localmente;
- sincronización programada desde la base Agrocalidad hacia Intercom.

## Mapa del módulo

La estructura interna de [Modules/Intercom](/home/cyan/Develop/interoperabilidad_intercom/Modules/Intercom:1) está bastante separada por responsabilidad:

```text
Modules/Intercom/
├── app/
│   ├── DTOs/
│   ├── Domain/Xml/
│   ├── Exceptions/
│   ├── Http/Controllers/
│   ├── Interfaces/
│   ├── Jobs/
│   ├── Models/
│   ├── Repositories/
│   └── Services/
├── config/
├── database/migrations/
├── resources/views/
├── routes/
│   ├── api.php
│   └── console.php
├── composer.json
└── module.json
```

## Cómo está organizado

### 1. Controladores HTTP

Los endpoints están en [Modules/Intercom/app/Http/Controllers](/home/cyan/Develop/interoperabilidad_intercom/Modules/Intercom/app/Http/Controllers:1) y se dividen en:

- `Auth/`: login y emisión de token.
- `CFE/`: operaciones de certificados fitosanitarios de exportación.
- `PFI/`: operaciones de permisos fitosanitarios de importación.

Patrón observado:

- el controlador recibe el request;
- arma DTOs o parámetros simples;
- delega la lógica a un servicio;
- devuelve XML, JSON o archivos según el caso.

### 2. Servicios de aplicación

La capa crítica está en [Modules/Intercom/app/Services](/home/cyan/Develop/interoperabilidad_intercom/Modules/Intercom/app/Services:1).

Servicios relevantes:

- `IntercomCfeService`: orquesta flujos CFE.
- `IntercomPfiService`: orquesta flujos PFI.
- `IntercomApiServices`: encapsula llamadas HTTP salientes a Intercom.
- `BaseIntercomService`: maneja configuración, cliente HTTP y token `client_credentials`.
- `AgrocalidadCFEDataService` y `AgrocalidadPFIDataService`: preparan datos provenientes de Agrocalidad.
- `XmlValidatorService`: valida estructuras XML por tipo de mensaje.
- `FormatosPendientesCANService`, `ErroresDocumentosRecibidosService`, `NotificacionesResultadosEnvioService`: persistencia de estados y errores.

### 3. Repositorios

La extracción de datos desde la base Agrocalidad está en:

- [AgrocalidadDBCFERepository.php](/home/cyan/Develop/interoperabilidad_intercom/Modules/Intercom/app/Repositories/AgrocalidadDBCFERepository.php:1)
- [AgrocalidadDBPFIRepository.php](/home/cyan/Develop/interoperabilidad_intercom/Modules/Intercom/app/Repositories/AgrocalidadDBPFIRepository.php:1)

Ambos usan `DB::connection('agrocalidad')`, así que el módulo depende de una segunda conexión PostgreSQL además de la base principal de Laravel.

### 4. Dominio XML

La parte más específica del proyecto está en [Modules/Intercom/app/Domain/Xml](/home/cyan/Develop/interoperabilidad_intercom/Modules/Intercom/app/Domain/Xml:1).

Ahí hay:

- `IntercomConstants`: catálogos, rutas de almacenamiento, códigos de estado y endpoints remotos;
- `Classes/XMLGenerator`: generador XML con estrategia;
- `Classes/CertificateAdapter` y `CertificateAdapterPFI`: transforman datos entre estructuras internas y XML;
- `Classes/XmlValidator`: validación contra XSD;
- `Strategies/`: estrategias separadas para CFE y PFI;
- `Composites/`: construcción de nodos XML con patrón composite.

Eso indica que el módulo no solo consume XML: también lo compone, lo transforma, lo valida y lo persiste.

### 5. Modelos propios del módulo

Además del modelo de autenticación, el módulo define modelos para:

- documentos CFE;
- documentos PFI;
- países interrelacionados;
- productos;
- clases, tratamientos y descripciones de paquetes;
- formatos pendientes;
- errores de documentos recibidos;
- notificaciones de resultados;
- tokens de acceso hacia Intercom.

Es decir: no es solo un gateway HTTP; también mantiene estado local del proceso de interoperabilidad.

## Flujo funcional del módulo

### Flujo de salida hacia Intercom

Para `MBA002` el patrón es:

1. leer documentos desde Agrocalidad;
2. enriquecer con productos y datos relacionados;
3. adaptar el payload al formato esperado;
4. generar XML;
5. validarlo y almacenarlo;
6. solicitar token remoto si hace falta;
7. enviarlo a Intercom;
8. registrar estados, errores o respuestas.

Este flujo existe tanto para CFE como para PFI.

### Flujo de entrada desde Intercom

Para mensajes como `MEX501`, `MEX502` y `MEX503`, el patrón observado es:

1. recibir XML vía endpoint del módulo;
2. validar estructura y contenido;
3. decodificar adjuntos si existen;
4. persistir documento, estado o error;
5. devolver XML de respuesta positiva o negativa según reglas internas.

Las respuestas de aceptación o rechazo están hardcodeadas en `IntercomConstants` como XML de salida estandarizado.

## Integraciones externas

### Agrocalidad

El módulo consume datos desde la conexión `agrocalidad` definida en [config/database.php](/home/cyan/Develop/interoperabilidad_intercom/config/database.php:1).

Variables involucradas:

```bash
AGROCALIDAD_DB_CONNECTION=agrocalidad
AGROCALIDAD_DB_HOST=
AGROCALIDAD_DB_PORT=5432
AGROCALIDAD_DB_DATABASE=
AGROCALIDAD_DB_USERNAME=
AGROCALIDAD_DB_PASSWORD=
```

### Intercom

El módulo usa `client_credentials` para autenticarse contra Intercom y cachea el token en base de datos local mediante el modelo `IntercomOAuthToken`.

Variables involucradas:

```bash
INTERCOM_CLIENT_ID=
INTERCOM_CLIENT_SECRET=
INTERCOM_REALM=
INTERCOM_AUTH_HOST=
INTERCOM_HOST=
```

`BaseIntercomService` construye la URL token como:

```text
{INTERCOM_AUTH_HOST}/realms/{INTERCOM_REALM}/protocol/openid-connect/token
```

## Autenticación de la API local

La API expuesta por el módulo usa Laravel Passport.

Puntos clave:

- el guard `api` usa driver `passport`;
- el provider `users` apunta a `Modules\Intercom\Models\User`;
- ese modelo autentica contra la tabla `users_can`;
- `findForPassport()` busca por la columna `usuario`;
- `/api/login` espera `application/x-www-form-urlencoded`;
- `/api/oauth/token` usa un controlador custom que elimina `refresh_token` de la respuesta.

Para que esto funcione necesitas:

- migraciones ejecutadas;
- claves de Passport generadas;
- un password grant client en `oauth_clients`;
- usuarios cargados en `users_can`.

## Endpoints del módulo

Rutas registradas desde [Modules/Intercom/routes/api.php](/home/cyan/Develop/interoperabilidad_intercom/Modules/Intercom/routes/api.php:1), confirmadas además con `php artisan route:list`.

### Auth

- `POST /api/login`
- `POST /api/oauth/token`

### CFE

- `POST /api/intercom-user/cfe/mba002-send-one`
- `POST /api/intercom-user/cfe/mba002-XML-download`
- `POST /api/intercom-user/cfe/mba005-consultar-estado-CFE`
- `POST /api/intercom-user/cfe/mba011-solicitar-envio-reenvio-CFE`
- `POST /api/intercom-user/cfe/RecibirCFEMEX501`
- `POST /api/intercom-user/cfe/estatusCFEMEX503`
- `POST /api/intercom-user/cfe/estatusCFEMEX502`
- `POST /api/intercom-user/cfe/descargar-archivo-pdf-cfi`

### PFI

- `POST /api/intercom-user/pfi/mba002-send-one`
- `POST /api/intercom-user/pfi/mba005-consultar-estado-PFI`
- `POST /api/intercom-user/pfi/mba011-solicitar-envio-reenvio-PFI`
- `POST /api/intercom-user/pfi/RecibirPFIMEX501`
- `POST /api/intercom-user/pfi/statusPFIMEX503`
- `POST /api/intercom-user/pfi/estatusPFIMEX502`
- `POST /api/intercom-user/pfi/descargar-archivo-pdf-pfi`

## Jobs y scheduler del módulo

El módulo registra tareas en [Modules/Intercom/routes/console.php](/home/cyan/Develop/interoperabilidad_intercom/Modules/Intercom/routes/console.php:1).

Jobs detectados:

- `ProcesarPhytosanitaryCertificateMBA002`
- `PermitePhytosanitaryImportMBA002`

Frecuencias configuradas:

- CFE `MBA002`: cada minuto.
- PFI `MBA002`: cada diez minutos.

Ambos jobs:

- leen documentos desde Agrocalidad;
- completan relaciones o productos;
- invocan servicios `IntercomCfeService` o `IntercomPfiService`;
- corren con `withoutOverlapping()` y `onOneServer()`;
- usan zona horaria `America/Guayaquil`.

## Persistencia y archivos del módulo

El módulo usa almacenamiento local, especialmente el disk `agrocalidad` definido en [config/filesystems.php](/home/cyan/Develop/interoperabilidad_intercom/config/filesystems.php:1).

Rutas funcionales importantes definidas en `IntercomConstants`:

- `certificadoFitoSanitarioExportacion/XML`
- `certificadoFitoSanitarioExportacion/PDF`
- `permisoFitoSanitarioImportacion/XML`
- `permisoFitoSanitarioImportacion/PDF`

El módulo:

- guarda XML generados o recibidos;
- guarda PDFs extraídos de mensajes;
- permite descargar esos archivos por endpoint;
- utiliza `storage/schemas` para validación XSD.

## Migraciones relevantes para `Intercom`

La migración propia del módulo es:

- [2024_12_16_215530_create_intercom_o_auth_tokens_table.php](/home/cyan/Develop/interoperabilidad_intercom/Modules/Intercom/database/migrations/2024_12_16_215530_create_intercom_o_auth_tokens_table.php:1)

Además depende fuertemente de migraciones de la app raíz:

- tablas OAuth de Passport;
- `users_can`;
- `jobs`, `failed_jobs`, `cache`.

## Instalación mínima para trabajar el módulo

### Variables de entorno críticas

Para Docker conviene corregir el ejemplo base y dejar algo así:

```bash
APP_URL=http://localhost

DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=intercom
DB_USERNAME=intercom
DB_PASSWORD=secret

REDIS_HOST=redis
REDIS_PORT=6379

QUEUE_CONNECTION=database

AGROCALIDAD_DB_CONNECTION=agrocalidad
AGROCALIDAD_DB_HOST=
AGROCALIDAD_DB_PORT=5432
AGROCALIDAD_DB_DATABASE=
AGROCALIDAD_DB_USERNAME=
AGROCALIDAD_DB_PASSWORD=

INTERCOM_CLIENT_ID=
INTERCOM_CLIENT_SECRET=
INTERCOM_REALM=
INTERCOM_AUTH_HOST=
INTERCOM_HOST=
```

### Puesta en marcha

```bash
cp .env.example .env
docker compose up -d --build
docker exec intercom_laravel php artisan migrate
docker exec intercom_laravel php artisan passport:keys
docker exec -it intercom_laravel php artisan passport:client --password
```

### Crear usuario API del módulo

```bash
docker exec -it intercom_laravel php artisan tinker
```

```php
\Modules\Intercom\Models\User::create([
    'name' => 'Administrador',
    'usuario' => 'admin',
    'email_verified_at' => null,
    'password' => bcrypt('secret'),
    'remember_token' => '',
]);
```

## Comandos útiles para explorar `Intercom`

```bash
docker exec intercom_laravel php artisan module:list
docker exec intercom_laravel php artisan route:list
docker exec intercom_laravel php artisan migrate:status
docker exec intercom_laravel php artisan optimize:clear
docker compose logs -f queue
docker compose logs -f scheduler
docker compose logs -f app_laravel
```

## Riesgos y observaciones del módulo

- El módulo depende de XML y catálogos hardcodeados, por lo que cambios de estándar impactan bastante.
- Parte de la integración remota usa `withOptions(['verify' => false])`; eso conviene revisarlo si se endurece seguridad TLS.
- El `README` original del repo no explicaba el dominio XML ni el flujo real de interoperabilidad; este documento sí está centrado en eso.
- La suite de pruebas visible aún no cubre de forma seria `Modules/Intercom`.
- El proyecto usa `container_name` fijos en Docker, lo que puede generar conflictos al recrear contenedores.

## Siguiente mejora recomendada

Si quieres que el README quede realmente operativo para nuevos desarrolladores del módulo, el siguiente paso lógico es agregar:

- ejemplos de request por endpoint;
- ejemplos de XML `MBA002`, `MEX501`, `MEX502`, `MEX503`;
- mapa de tablas que usa cada servicio;
- diagrama del flujo `Agrocalidad -> Intercom -> persistencia local`.
