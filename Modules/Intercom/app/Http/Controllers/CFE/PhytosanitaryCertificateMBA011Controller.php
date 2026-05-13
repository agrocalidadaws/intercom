<?php

namespace Modules\Intercom\Http\Controllers\CFE;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Intercom\DTOs\ParametrosSolicitudDTO;
use Modules\Intercom\Services\IntercomCfeService;

class PhytosanitaryCertificateMBA011Controller extends Controller {

    public function __construct() {
        $this->intercomCfeService = new IntercomCfeService();
    }

    public function solicitarEnvioReenvio(Request $request) {
        $parametrosSolicitud = new ParametrosSolicitudDTO(
            '','',''
        );

        if (!empty($request->all())) {
            $parametrosSolicitud = new ParametrosSolicitudDTO(
                $request->idFormato==null?'':$request->idFormato, 
                $request->codigoFormato==null?'':$request->codigoFormato,
                $request->puntoOrigen
            );
        }

        $result = $this->intercomCfeService -> solicitarEnvioReenvioMbamba011($parametrosSolicitud);
        return $result;
    }

}