<?php

namespace Modules\Intercom\Http\Controllers\PFI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Intercom\DTOs\ParametrosBusquedaDTO;
use Modules\Intercom\Services\IntercomPfiService;

class PhytosanitaryImportMBA005Controller extends Controller {

    public function __construct() {
        $this->intercomPfiService = new IntercomPfiService();
    }

    public function getPhytosanitaryImport(Request $request){
        $parametrosBusqueda = new ParametrosBusquedaDTO(
            '',
            '',
            '',
            0,
            0,
        );
        if (!empty($request->all())) {
            $parametrosBusqueda = new ParametrosBusquedaDTO(
                $request->puntoOrigen==null?'':$request->puntoOrigen,
                $request->fechaEnvioDesde==null?'':$request->fechaEnvioDesde,
                $request->fechaEnvioHasta==null?'':$request->fechaEnvioHasta,
                (int)$request->registrosPagina,
                (int)$request->numeroPagina,
            );
        }

        $result = $this->intercomPfiService->getListPhytonsanitaryImportPermitMba005($parametrosBusqueda);
        return $result;
    }

}