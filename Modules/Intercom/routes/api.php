<?php

use Illuminate\Support\Facades\Route;
use Modules\Intercom\Http\Controllers\Auth\CustomAccessTokenController;
use Modules\Intercom\Http\Controllers\Auth\LoginController;
use Modules\Intercom\Http\Controllers\CFE\PhytosanitaryCertificateMBA002Controller;
use Modules\Intercom\Http\Controllers\CFE\PhytosanitaryCertificateMBA005Controller;
use Modules\Intercom\Http\Controllers\CFE\PhytosanitaryCertificateMBA011Controller;
use Modules\Intercom\Http\Controllers\CFE\PhytosanitaryCertificateMEX501Controller;
use Modules\Intercom\Http\Controllers\CFE\PhytosanitaryCertificateMEX502Controller;
use Modules\Intercom\Http\Controllers\CFE\PhytosanitaryCertificateMEX503Controller;
use Modules\Intercom\Http\Controllers\PFI\PhytosanitaryImportMBA002Controller;
use Modules\Intercom\Http\Controllers\PFI\PhytosanitaryImportMBA005Controller;
use Modules\Intercom\Http\Controllers\PFI\PhytosanitaryImportMBA011Controller;
use Modules\Intercom\Http\Controllers\PFI\PhytosanitaryImportMBA023Controller;

/*
 *--------------------------------------------------------------------------
 * API Routes
 *--------------------------------------------------------------------------
 *
 * Here is where you can register API routes for your application. These
 * routes are loaded by the RouteServiceProvider within a group which
 * is assigned the "api" middleware group. Enjoy building your API!
 *
*/

Route::post('/oauth/token', [CustomAccessTokenController::class, 'issueToken'])
    ->middleware(['throttle']);


Route::post('/login', [LoginController::class, 'login']);
Route::middleware('auth:api')->group(function () {

    Route::prefix('intercom-user')->group(function () {

        Route::prefix('cfe')->group(function () {
            Route::post('mba002-send-one', [PhytosanitaryCertificateMBA002Controller::class, 'sendExportPhytosanitaryCertificate']);
            Route::post('mba002-XML-download', [PhytosanitaryCertificateMBA002Controller::class, 'descargarXMLFitosanitario']);
            Route::post('mba005-consultar-estado-CFE', [PhytosanitaryCertificateMBA005Controller::class, 'getPhytosanitaryCertificate']);
            Route::post('mba011-solicitar-envio-reenvio-CFE', [PhytosanitaryCertificateMBA011Controller::class, 'solicitarEnvioReenvio']);
            Route::post('RecibirCFEMEX501', [PhytosanitaryCertificateMEX501Controller::class, 'recibirDocumentoCFEMBA002PorMEX501']);
            Route::post('estatusCFEMEX503', [PhytosanitaryCertificateMEX503Controller::class, 'estatusDocumentoCFEMBA002PorMEX503']);
            Route::post('estatusCFEMEX502', [PhytosanitaryCertificateMEX502Controller::class, 'estatusDocumentoCFEMBA002PorMEX502']);
            Route::post('descargar-archivo-pdf-cfi', [PhytosanitaryCertificateMEX501Controller::class, 'descargarArchivoPdfCfe']);
        });

        Route::prefix('pfi')->group(function () {
            Route::post('mba002-send-one', [PhytosanitaryImportMBA002Controller::class, 'sendPermitPhytosanitaryImport']);
            Route::post('mba005-consultar-estado-PFI', [PhytosanitaryImportMBA005Controller::class, 'getPhytosanitaryImport']);
            Route::post('mba011-solicitar-envio-reenvio-PFI', [PhytosanitaryImportMBA011Controller::class, 'solicitarEnvioReenvio']);
            Route::post('RecibirPFIMEX501', [PhytosanitaryCertificateMEX501Controller::class, 'recibirDocumentoPFIMBA002PorMEX501']);
            Route::post('statusPFIMEX503', [PhytosanitaryCertificateMEX503Controller::class, 'estatusDocumentoPFIMBA002PorMEX503']);
            Route::post('estatusPFIMEX502', [PhytosanitaryCertificateMEX502Controller::class, 'estatusDocumentoPFIMBA002PorMEX502']);
            Route::post('descargar-archivo-pdf-pfi', [PhytosanitaryCertificateMEX501Controller::class, 'descargarArchivoPdfPfi']);
        });
    });
});
