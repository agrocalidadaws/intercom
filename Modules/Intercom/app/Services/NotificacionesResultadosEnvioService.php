<?php

namespace Modules\Intercom\Services;

use Modules\Intercom\Models\NotificacionesResultadosEnvio;
use Illuminate\Support\Facades\DB;
use Modules\Intercom\Domain\Xml\IntercomConstants;
use Illuminate\Support\Facades\Log;

class NotificacionesResultadosEnvioService
{

    public function guardarNotificacionesResultadoEnvio($notificacionesResultadosEnvioDAO)
    {
        DB::beginTransaction();
        try {
            $notificacionesResultadosEnvio = NotificacionesResultadosEnvio::create([
                'id_solicitud' => $notificacionesResultadosEnvioDAO->idSolicitud,
                'codigo_formato' => $notificacionesResultadosEnvioDAO->codigoFormato,
                'punto_destino' => $notificacionesResultadosEnvioDAO->puntoDestino,
                'fecha_recepcion_intercom' => $notificacionesResultadosEnvioDAO->fechaRecepcionIntercom,
                'fecha_recepcion_destino' => $notificacionesResultadosEnvioDAO->fechaRecepcionDestino,
                'estado_documento' => $notificacionesResultadosEnvioDAO->estadoDocumento,
                'supero_cantidad_intentos' => $notificacionesResultadosEnvioDAO->superoCantidadIntento,
                'tipo_documento' => $notificacionesResultadosEnvioDAO->tipoDocumento
            ]);

            $erroresDocumentosEnviados = $notificacionesResultadosEnvioDAO->erroresNotificacionesResultadoEnvio;
            $listaErroresDocumentos = [];
            foreach ($erroresDocumentosEnviados as $errorDocumento) {
                $erroresNotificacionesResultadoEnvio = [
                    'codigo_error' => $errorDocumento->codigoError,
                    'detalle_error' => $errorDocumento->detalleError
                ];
                $listaErroresDocumentos[] = $erroresNotificacionesResultadoEnvio;
            }

            $notificacionesResultadosEnvio->erroresNotificacionesResultadoEnvio()->createMany($listaErroresDocumentos);

            DB::commit();
            return IntercomConstants::RESPUESTA_INTERCOM_POSITIVA_MEX502;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::info('Error clase NotificacionesResultadosEnvioService metodo guardarNotificacionesResultadoEnvio: ' . $e->getMessage());
            return  str_replace('%error%', 'Error: '.$e->getMessage(), IntercomConstants::RESPUESTA_INTERCOM_NEGATIVA_MEX502);
        }
    }
}
