<?php

namespace Modules\Intercom\Services;

use Modules\Intercom\DTOs\InteroperabilidadCANDTO;
use Modules\Intercom\Models\InteroperabilidadCAN;
use Illuminate\Support\Facades\Log;

class InteroperabilidadCANService
{

    public function guardarInteroperabilidad(InteroperabilidadCANDTO $interoperabilidadCANDTO)
    {
        try {
            $interopeabilidad = InteroperabilidadCAN::create([
                'id_certificado_permiso' => $interoperabilidadCANDTO->idCertificadoPermiso,
                'codigo_fitosanitario_o' => $interoperabilidadCANDTO->codigoFitosanitarioO,
                'codigo_fitosanitario_c' => $interoperabilidadCANDTO->codigoFitosanitarioC,
                'tipo_documento' => $interoperabilidadCANDTO->tipoDocumento,
                'metodo' => $interoperabilidadCANDTO->metodo,
                'estado_documento' => $interoperabilidadCANDTO->estadoDocumento,
                'respuesta_intercom' => $interoperabilidadCANDTO->respuestaIntercom,
                'parth_archivo' => $interoperabilidadCANDTO->parthArchivo,
                'ejecutado_por' => $interoperabilidadCANDTO->ejecutadoPor
            ]);

            return response()->json([
                'message' => 'Interoperabilidad registrada con exito',
                'interoperabilidad' => $interopeabilidad
            ]);
        } catch (\Exception $e) {
            Log::info('Error clase InteroperabilidadCANService metodo guardarInteroperabilidad: ' . $e->getMessage());
            return response()->json([
                'message' => 'Interoperabilidad con errores: ' .  $e->getMessage(),
            ]);;
        }
    }

    public function actualizarInteroperabilidad($interopeabilidad, $estado)
    {
        try {
            $interopeabilidad->estado_documento = $estado;
            $intero = $interopeabilidad->save();
            return $intero;
        } catch (\Exception $e) {
            Log::info('Error clase InteroperabilidadCANService metodo actualizarInteroperabilidad: ' . $e->getMessage());
            return $interopeabilidad;
        }
    }

    public function buscarFitosanitarioPorId($idCertificadoPermiso, $documento, $metodo)
    {
        try {
            $interopeabilidad = InteroperabilidadCAN::where('id_certificado_permiso', '=', $idCertificadoPermiso)
                ->where('tipo_documento', '=', $documento)
                ->where('metodo', '=', $metodo)
                ->get();

            return $interopeabilidad;
        } catch (\Exception $e) {
            Log::info('Error clase InteroperabilidadCANService metodo buscarFitosanitarioPorId: ' . $e->getMessage());
            return [];
        }
    }

    public function buscarXMLFitosanitario($idCertificadoPermiso, $codigoCertificadoO, $tipoMetodo)
    {
        try {
            $rutaArchivo = InteroperabilidadCAN::where('id_certificado_permiso', '=', $idCertificadoPermiso)
                ->where('codigo_fitosanitario_o', '=', $codigoCertificadoO)
                ->where('metodo', '=', $tipoMetodo)
                ->select('parth_archivo')
                ->get();

            return $rutaArchivo;
        } catch (\Exception $e) {
            Log::info('Error clase InteroperabilidadCANService metodo buscarXMLFitosanitario: ' . $e->getMessage());
            return [];
        }
    }
}
