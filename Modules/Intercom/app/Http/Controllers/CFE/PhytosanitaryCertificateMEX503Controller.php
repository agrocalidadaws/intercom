<?php

namespace Modules\Intercom\Http\Controllers\CFE;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Intercom\Services\IntercomCfeService;
use Modules\Intercom\Services\IntercomPfiService;

class PhytosanitaryCertificateMEX503Controller extends Controller {

    public function __construct()
    {
        $this->intercomCfeService = new IntercomCfeService();
        $this->intercomPfiService = new IntercomPfiService();
    }

    public function estatusDocumentoCFEMBA002PorMEX503(Request $request) {

        $xmlContent = $request->getContent();

        $result=$this->intercomCfeService->statusObtenerCFEMEX503($xmlContent);

        return $result;
    }

    public function estatusDocumentoPFIMBA002PorMEX503(Request $request) {

        $xmlContent = $request->getContent();

        $result=$this->intercomPfiService->statusObtenerPFIMEX503($xmlContent);

        return $result;
    }

}