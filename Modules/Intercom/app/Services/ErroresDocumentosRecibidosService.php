<?php

namespace Modules\Intercom\Services;

use Illuminate\Support\Facades\Log;
use Modules\Intercom\Models\ErroresDocumentosRecibidos;

class ErroresDocumentosRecibidosService
{

    public function guardarErroresDocumentosService($erroreDocumentoRecibidoDTO)
    {
        try {
            ErroresDocumentosRecibidos::create(
                [
                    'codigo_formato' => $erroreDocumentoRecibidoDTO->codigoFormato,
                    'fecha_emision' => $erroreDocumentoRecibidoDTO->fechaEmision,
                    'punto_origen' => $erroreDocumentoRecibidoDTO->puntoOrigen,
                    'error_presentado' => $erroreDocumentoRecibidoDTO->errorPresentado,
                    'tipo_documento' => $erroreDocumentoRecibidoDTO->tipoDocumento,
                    'estado_envio' => $erroreDocumentoRecibidoDTO->estadoEnvio
                ]
            );
        } catch (\Exception $e) {
            Log::info('Error clase ErroresDocumentosRecibidosService metodo guardarErroresDocumentosService: ' . $e->getMessage());
        }
    }

    public function obtenerErrorPorEstado($tipoDocumento)
    {
        try {
            $erroresDocumentosRecibidos = ErroresDocumentosRecibidos::where('estado_envio', '=', 'Pendiente')
                ->where('tipo_documento', '=', $tipoDocumento)->get();
            return $erroresDocumentosRecibidos;
        } catch (\Exception $e) {
            Log::info('Error clase ErroresDocumentosRecibidosService metodo obtenerErrorPorEstado: ' . $e->getMessage());
            return [];
        }
    }

    public function actualizarErroresDocumentosRecibidos($erroresDocumentosRecibidos)
    {
        try {
            $erroresDocumentosRecibidos->save($erroresDocumentosRecibidos);
        } catch (\Exception $e) {
            Log::info('Error clase ErroresDocumentosRecibidosService metodo actualizarErroresDocumentosRecibidos: ' . $e->getMessage());
            return [];
        }
    }
}
