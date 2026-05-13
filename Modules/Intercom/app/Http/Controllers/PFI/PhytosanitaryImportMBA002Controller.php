<?php

namespace Modules\Intercom\Http\Controllers\PFI;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\Intercom\Repositories\AgrocalidadDBPFIRepository;
use Modules\Intercom\Services\AgrocalidadPFIDataService;
use Modules\Intercom\Services\IntercomPfiService;

class PhytosanitaryImportMBA002Controller extends Controller {

    public function __construct()
    {
        $this->agrocalidadDataService = new AgrocalidadPFIDataService(new AgrocalidadDBPFIRepository);
        $this->intercomPfiService = new IntercomPfiService();
    }

    public function fetchAllExportPhytosanitaryCertificates()
    {
        $result = $this->agrocalidadDataService->fetchAllPhytosanitaryImportPermit();
        $result_response = $this->intercomPfiService->sendPhytonsanitaryImportPermitMba002($result);
        return $result_response;
    }

    public function sendPermitPhytosanitaryImport(Request $request)
    {
        $perms_id = $request->input('permiso_id');
        $result = $this->agrocalidadDataService->fetchAllPhytosanitaryImportPermitId($perms_id);
       /* $result_response = $this->intercomPfiService->sendPhytonsanitaryImportPermitMba002($result);*/
        return $result;
    }
}