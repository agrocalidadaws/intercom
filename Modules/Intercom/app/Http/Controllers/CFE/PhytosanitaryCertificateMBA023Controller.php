<?php

namespace Modules\Intercom\Http\Controllers\CFE;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Intercom\DTOs\ParametrosRecepcionDocumentoDTO;
use Modules\Intercom\Services\CertificadoCFECANService;
use Modules\Intercom\Services\ErroresDocumentosRecibidosService;
use Modules\Intercom\Services\IntercomCfeService;

class PhytosanitaryCertificateMBA023Controller extends Controller
{

    public function __construct()
    {
        $this->certificadoCFECANService = new CertificadoCFECANService();
        $this->intercomCfeService = new IntercomCfeService();
    }

    public function resultadoRecepcionDocumento(Request $request)
    {
        $result=null;
        $certificadoListaCFE = $this->certificadoCFECANService->obtenerCFIPoEnvioRecepcionDocumento();
        if (!empty($certificadoListaCFE)) {
            foreach ($certificadoListaCFE as $certificadoCFE) {
                $result = $this->intercomCfeService->solicitarEnvioReenvioMbamba023($certificadoCFE);
            }
        } else {
            $erroresDocumentosRecibidosService = new ErroresDocumentosRecibidosService();
            $listaErroresDocumentos = $erroresDocumentosRecibidosService->obtenerErrorPorEstado('CFI');
            foreach ($listaErroresDocumentos as $erroresDocumentos) {
                $this->intercomCfeService->enviarErrorDocumentoMbamba023($erroresDocumentos);
            }
        }

        return $result;
    }
}
