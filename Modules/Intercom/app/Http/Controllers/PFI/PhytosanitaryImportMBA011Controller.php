<?php

namespace Modules\Intercom\Http\Controllers\PFI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Intercom\DTOs\ParametrosSolicitudDTO;
use Modules\Intercom\Services\IntercomPfiService;

class PhytosanitaryImportMBA011Controller extends Controller {
    
    public function __construct() {
        $this->intercomPfiService = new IntercomPfiService();
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

        $result = $this->intercomPfiService -> solicitarEnvioReenvioMbamba011($parametrosSolicitud);
        return $result;
    }

}
