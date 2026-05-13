<?php

namespace Modules\Intercom\Http\Controllers\CFE;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Intercom\Services\IntercomCfeService;
use Modules\Intercom\Services\IntercomPfiService;

class PhytosanitaryCertificateMEX502Controller extends Controller {

    public function __construct()
    {
        $this->intercomCfeService = new IntercomCfeService();
        $this->intercomPfiService = new IntercomPfiService();
    }

    public function estatusDocumentoCFEMBA002PorMEX502 (Request $request) {
        $xmlContent = $request->getContent();
        $result=$this->intercomCfeService->statusObtenerCFEMEX502($xmlContent);
        return $result;
    }

    public function estatusDocumentoPFIMBA002PorMEX502 (Request $request) {
        $xmlContent = $request->getContent();
        $result=$this->intercomPfiService->statusObtenerPFIMEX502($xmlContent);
        return $result;
    }

}