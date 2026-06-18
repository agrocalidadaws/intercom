# Documentacion de Rutas

Documento generado a partir de `php artisan route:list` y de la lectura de los controladores del proyecto.

## Resumen

- Total de rutas detectadas: 34
- Rutas del modulo Intercom: autenticacion local, CFE y PFI
- Rutas de Passport: autorizacion, tokens y gestion de clientes OAuth
- Rutas internas de framework: `storage/{path}` y `up`

## Reglas generales

- Todas las rutas bajo `api/intercom-user/*` requieren `auth:api`.
- `POST /api/login` es el login local del modulo y delega internamente a Passport.
- `POST /api/oauth/token` es el endpoint local de Passport expuesto por un controlador custom que elimina `refresh_token` de la respuesta.
- Las rutas `oauth/*` son las rutas standard registradas por Laravel Passport.

## Auth

### `POST /api/login`

- Controlador: `Modules\Intercom\Http\Controllers\Auth\LoginController@login`
- Proposito: autentica un usuario local de `users_can` usando `client_id` como `usuario` y `client_secret` como `password`.
- Comportamiento: reenvia la autenticacion a `POST /api/oauth/token` usando Password Grant de Passport.
- Requiere: `Content-Type: application/x-www-form-urlencoded`

### `POST /api/oauth/token`

- Controlador: `Modules\Intercom\Http\Controllers\Auth\CustomAccessTokenController@issueToken`
- Proposito: emite un access token de Passport para la API local.
- Comportamiento: delega al `AccessTokenController` de Passport y remueve `refresh_token` de la respuesta JSON.

## CFE

Base protegida: `POST /api/intercom-user/cfe/*`

### `POST /api/intercom-user/cfe/mba002-send-one`

- Controlador: `PhytosanitaryCertificateMBA002Controller@sendExportPhytosanitaryCertificate`
- Proposito: obtiene un certificado fitosanitario de exportacion desde Agrocalidad por `certificate_id` y lo envia a Intercom usando el flujo MBA002.

### `POST /api/intercom-user/cfe/mba002-XML-download`

- Controlador: `PhytosanitaryCertificateMBA002Controller@descargarXMLFitosanitario`
- Proposito: recupera el XML previamente generado o almacenado para interoperabilidad.
- Parametros usados por el controlador: `id_certificado_permiso`, `codigo_fitosanitario_c`, `tipo_metodo`.

### `POST /api/intercom-user/cfe/mba005-consultar-estado-CFE`

- Controlador: `PhytosanitaryCertificateMBA005Controller@getPhytosanitaryCertificate`
- Proposito: consulta en Intercom el listado o estado de certificados CFE enviados.
- Filtros soportados: `puntoOrigen`, `fechaEnvioDesde`, `fechaEnvioHasta`, `registrosPagina`, `numeroPagina`.

### `POST /api/intercom-user/cfe/mba011-solicitar-envio-reenvio-CFE`

- Controlador: `PhytosanitaryCertificateMBA011Controller@solicitarEnvioReenvio`
- Proposito: solicita envio o reenvio de un formato CFE por el flujo MBA011.
- Parametros usados: `idFormato`, `codigoFormato`, `puntoOrigen`.

### `POST /api/intercom-user/cfe/RecibirCFEMEX501`

- Controlador: `PhytosanitaryCertificateMEX501Controller@recibirDocumentoCFEMBA002PorMEX501`
- Proposito: recibe desde Intercom un documento CFE en XML asociado al flujo MEX501.
- Comportamiento: lee el body crudo del request y lo entrega al servicio de CFE.

### `POST /api/intercom-user/cfe/estatusCFEMEX503`

- Controlador: `PhytosanitaryCertificateMEX503Controller@estatusDocumentoCFEMBA002PorMEX503`
- Proposito: procesa una notificacion XML de estatus para CFE bajo el flujo MEX503.

### `POST /api/intercom-user/cfe/estatusCFEMEX502`

- Controlador: `PhytosanitaryCertificateMEX502Controller@estatusDocumentoCFEMBA002PorMEX502`
- Proposito: procesa una respuesta XML de estatus o consulta para CFE bajo el flujo MEX502.

### `POST /api/intercom-user/cfe/descargar-archivo-pdf-cfi`

- Controlador: `PhytosanitaryCertificateMEX501Controller@descargarArchivoPdfCfe`
- Proposito: descarga el PDF asociado a un certificado CFE.
- Parametro usado: `id_certificado`

## PFI

Base protegida: `POST /api/intercom-user/pfi/*`

### `POST /api/intercom-user/pfi/mba002-send-one`

- Controlador: `PhytosanitaryImportMBA002Controller@sendPermitPhytosanitaryImport`
- Proposito: obtiene un permiso fitosanitario de importacion por `permiso_id`.
- Observacion: en el controlador actual el envio a Intercom esta comentado; hoy devuelve el permiso consultado en Agrocalidad.

### `POST /api/intercom-user/pfi/mba005-consultar-estado-PFI`

- Controlador: `PhytosanitaryImportMBA005Controller@getPhytosanitaryImport`
- Proposito: consulta en Intercom el listado o estado de permisos PFI enviados.
- Filtros soportados: `puntoOrigen`, `fechaEnvioDesde`, `fechaEnvioHasta`, `registrosPagina`, `numeroPagina`.

### `POST /api/intercom-user/pfi/mba011-solicitar-envio-reenvio-PFI`

- Controlador: `PhytosanitaryImportMBA011Controller@solicitarEnvioReenvio`
- Proposito: solicita envio o reenvio de un formato PFI por el flujo MBA011.
- Parametros usados: `idFormato`, `codigoFormato`, `puntoOrigen`.

### `POST /api/intercom-user/pfi/RecibirPFIMEX501`

- Controlador: `PhytosanitaryCertificateMEX501Controller@recibirDocumentoPFIMBA002PorMEX501`
- Proposito: recibe desde Intercom un documento PFI en XML asociado al flujo MEX501.

### `POST /api/intercom-user/pfi/statusPFIMEX503`

- Controlador: `PhytosanitaryCertificateMEX503Controller@estatusDocumentoPFIMBA002PorMEX503`
- Proposito: procesa una notificacion XML de estatus para PFI bajo el flujo MEX503.

### `POST /api/intercom-user/pfi/estatusPFIMEX502`

- Controlador: `PhytosanitaryCertificateMEX502Controller@estatusDocumentoPFIMBA002PorMEX502`
- Proposito: procesa una respuesta XML de estatus o consulta para PFI bajo el flujo MEX502.

### `POST /api/intercom-user/pfi/descargar-archivo-pdf-pfi`

- Controlador: `PhytosanitaryCertificateMEX501Controller@descargarArchivoPdfPfi`
- Proposito: descarga el PDF asociado a un permiso PFI.
- Parametro usado: `id_permiso`

## Passport

Estas rutas son registradas por Laravel Passport para administracion OAuth2.

### `GET|HEAD /oauth/authorize`

- Nombre: `passport.authorizations.authorize`
- Proposito: muestra o resuelve la pantalla de autorizacion OAuth para terceros.

### `POST /oauth/authorize`

- Nombre: `passport.authorizations.approve`
- Proposito: aprueba una solicitud de autorizacion OAuth.

### `DELETE /oauth/authorize`

- Nombre: `passport.authorizations.deny`
- Proposito: rechaza una solicitud de autorizacion OAuth.

### `GET|HEAD /oauth/clients`

- Nombre: `passport.clients.index`
- Proposito: lista clientes OAuth del usuario autenticado.

### `POST /oauth/clients`

- Nombre: `passport.clients.store`
- Proposito: crea un nuevo cliente OAuth.

### `PUT /oauth/clients/{client_id}`

- Nombre: `passport.clients.update`
- Proposito: actualiza un cliente OAuth existente.

### `DELETE /oauth/clients/{client_id}`

- Nombre: `passport.clients.destroy`
- Proposito: elimina un cliente OAuth existente.

### `GET|HEAD /oauth/personal-access-tokens`

- Nombre: `passport.personal.tokens.index`
- Proposito: lista personal access tokens.

### `POST /oauth/personal-access-tokens`

- Nombre: `passport.personal.tokens.store`
- Proposito: crea un personal access token.

### `DELETE /oauth/personal-access-tokens/{token_id}`

- Nombre: `passport.personal.tokens.destroy`
- Proposito: revoca un personal access token.

### `GET|HEAD /oauth/scopes`

- Nombre: `passport.scopes.index`
- Proposito: lista scopes disponibles.

### `POST /oauth/token`

- Nombre: `passport.token`
- Proposito: endpoint OAuth2 standard de Passport para emitir tokens.
- Nota: es distinto de `POST /api/oauth/token`, que es la variante del modulo bajo prefijo `/api`.

### `POST /oauth/token/refresh`

- Nombre: `passport.token.refresh`
- Proposito: refresca un token OAuth.

### `GET|HEAD /oauth/tokens`

- Nombre: `passport.tokens.index`
- Proposito: lista access tokens del usuario autenticado.

### `DELETE /oauth/tokens/{token_id}`

- Nombre: `passport.tokens.destroy`
- Proposito: revoca un access token.

## Framework y soporte

### `GET|HEAD /storage/{path}`

- Nombre: `storage.agrocalidad`
- Proposito: expone archivos almacenados por la aplicacion.
- Observacion: no se identifico una definicion local explicita en el codigo leido; parece una ruta auxiliar registrada por la app o por infraestructura del proyecto.

### `GET|HEAD /up`

- Proposito: health check de Laravel.
- Origen: definido en `bootstrap/app.php` con `health: '/up'`.

## Comando de referencia

Para regenerar el inventario base:

```bash
php artisan route:list
```

Si quieres contrastarlo con el contenedor en ejecucion:

```bash
docker exec intercom_laravel php artisan route:list
```
