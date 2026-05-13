<?php

namespace Modules\Intercom\Http\Controllers\CFE;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Intercom\DTOs\ParametrosBusquedaDTO;
use Modules\Intercom\Services\IntercomCfeService;

use function PHPUnit\Framework\isEmpty;

class PhytosanitaryCertificateMBA005Controller extends Controller {

    public function __construct() {
        $this->intercomCfeService = new IntercomCfeService();
    }

    public function getPhytosanitaryCertificate(Request $request){
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
        
        $result = $this->intercomCfeService ->getListExportPhytosanitaryCertificateMba005($parametrosBusqueda);
        return $result;
    }

}