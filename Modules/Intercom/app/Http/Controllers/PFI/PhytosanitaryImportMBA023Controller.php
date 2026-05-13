<?php

namespace Modules\Intercom\Http\Controllers\PFI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Intercom\DTOs\ParametrosRecepcionDocumentoDTO;
use Modules\Intercom\Services\ErroresDocumentosRecibidosService;
use Modules\Intercom\Services\IntercomPfiService;
use Modules\Intercom\Services\PermisoPFICANService;

class PhytosanitaryImportMBA023Controller extends Controller
{

    public function __construct()
    {
        $this->permisoPFICANService = new PermisoPFICANService();
        $this->intercomPfiService = new IntercomPfiService();
    }

    public function resultadoRecepcionDocumento(Request $request)
    {
        $result=null;
        $permisoListPFI = $this->permisoPFICANService->obtenerCFIPoEnvioRecepcionDocumento();
        if (!empty($permisoListPFI)) {
            foreach ($permisoListPFI as $permisoPFI) {
                $result = $this->intercomPfiService->solicitarEnvioReenvioMbamba023($permisoPFI);
            }
        } else {
            $erroresDocumentosRecibidosService = new ErroresDocumentosRecibidosService();
            $listaErroresDocumentos = $erroresDocumentosRecibidosService->obtenerErrorPorEstado('PFI');
            foreach ($listaErroresDocumentos as $erroresDocumentos) {
                $result = $this->intercomPfiService->enviarErrorDocumentoMba023($erroresDocumentos);
            }
        }

        return $result;
    }
}
