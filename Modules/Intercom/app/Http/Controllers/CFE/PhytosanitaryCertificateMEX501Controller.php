<?php

namespace Modules\Intercom\Http\Controllers\CFE;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Intercom\Services\IntercomCfeService;
use Modules\Intercom\Services\IntercomPfiService;

class PhytosanitaryCertificateMEX501Controller extends Controller {

    public function __construct()
    {
        $this->intercomCfeService = new IntercomCfeService();
        $this->intercomPfiService = new IntercomPfiService();
    }

    public function recibirDocumentoCFEMBA002PorMEX501(Request $request) {

        // Leer el contenido del body
        $xmlContent = $request->getContent();

        $result=$this->intercomCfeService->recibirCFEMba002MEX501($xmlContent);

        return $result;
    }

    public function recibirDocumentoPFIMBA002PorMEX501(Request $request) {

        // Leer el contenido del body
        $xmlContent = $request->getContent();

        $result=$this->intercomPfiService->recibirPFIMba002MEX501($xmlContent);

        return $result;
    }

    public function descargarArchivoPdfCfe(Request $request) {

        $idCfe = $request->input('id_certificado');
        $result=$this->intercomCfeService->descargarPdfCertificado($idCfe);
        return $result;
    }

    public function descargarArchivoPdfPfi(Request $request) {

        $idPfi = $request->input('id_permiso');
        $result=$this->intercomPfiService->descargarPdfPermiso($idPfi);
        return $result;
    }

}