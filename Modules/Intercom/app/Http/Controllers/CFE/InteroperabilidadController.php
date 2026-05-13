<?php

use App\Http\Controllers\Controller;
use GuzzleHttp\Psr7\Request;

class InteroperabilidadController extends Controller
{

    public function guardarInteroperabilidad(Request $request)
    {

        $request->validate([
            'idCertificadoFitosanitario' => 'required|int',
            'codigoCertificado' => 'required|string|max:32',
            'tipoDocumento' => 'required|string|max:3',
            'metodo' => 'required|string|max:10',
            'estadoDocumento' => 'required|string|max:32',
            'respuestaIntercom' => 'required|string',
            'parthArchivo' => 'required|string|max:32'
        ]);

        $interopeabilidad = InteroperabilidadCAN::create([
            'id_certificado_fitosanitario' => $request->idCertificadoFitosanitario,
            'codigo_certificado'=> $request->codigoCertificado,
            'tipo_documento' => $request->tipoDocumento,
            'metodo' => $request->metodo,
            'estado_documento' => $request->estadoDocumento,
            'respuesta_intercom' => $request->respuestaIntercom,
            'parth_archivo' => $request->parthArchivo
        ]);

        return response()->json([
            'message' => 'Interoperabilidad registrada con exito',
            'interoperabilidad' => $interopeabilidad
        ]);
    }
}
