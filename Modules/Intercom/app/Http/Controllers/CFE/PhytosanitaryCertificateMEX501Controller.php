<?php

namespace Modules\Intercom\Http\Controllers\CFE;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Intercom\Services\IntercomCfeService;
use Modules\Intercom\Services\IntercomPfiService;

class PhytosanitaryCertificateMEX501Controller extends Controller
{

    public function __construct(
        private readonly IntercomCfeService $intercomCfeService,
        private readonly IntercomPfiService $intercomPfiService,
    ) {
    }

    public function recibirDocumentoCFEMBA002PorMEX501(Request $request): Response
    {
        $xmlContent = $request->getContent();
        $result = $this->intercomCfeService->recibirCFEMba002MEX501($xmlContent);

        return $this->xmlResponse($result);
    }

    public function recibirDocumentoPFIMBA002PorMEX501(Request $request): Response
    {
        $xmlContent = $request->getContent();
        $result = $this->intercomPfiService->recibirPFIMba002MEX501($xmlContent);

        return $this->xmlResponse($result);
    }

    public function descargarArchivoPdfCfe(Request $request)
    {
        $idCfe = $request->input('id_certificado');
        $result = $this->intercomCfeService->descargarPdfCertificado($idCfe);

        return $result;
    }

    public function descargarArchivoPdfPfi(Request $request)
    {
        $idPfi = $request->input('id_permiso');
        $result = $this->intercomPfiService->descargarPdfPermiso($idPfi);

        return $result;
    }

    private function xmlResponse(string $content): Response
    {
        return response($content)
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

}
