<?php

namespace Modules\Intercom\Http\Controllers\CFE;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Http\Controllers\Controller;

use Modules\Intercom\Repositories\AgrocalidadDBCFERepository;
use Modules\Intercom\Services\AgrocalidadCFEDataService;
use Modules\Intercom\Services\IntercomCfeService;

use function PHPUnit\Framework\isEmpty;

class PhytosanitaryCertificateMBA002Controller extends Controller
{
    public $agrocalidadDataService;

    public function __construct()
    {
        $this->agrocalidadDataService = new AgrocalidadCFEDataService(new AgrocalidadDBCFERepository);
        $this->intercomCfeService = new IntercomCfeService();
    }

    public function fetchAllExportPhytosanitaryCertificates()
    {
        $result = $this->agrocalidadDataService->fetchAllExportPhytosanitaryCertificatesData();

        return $this->responseJson($result);
    }

    public function fetchExportPhytosanitaryCertificate(Request $request)
    {
        $cert_id = $request->input('cert_id');
        $result = $this->agrocalidadDataService->fetchExportPhytosanitaryCertificateData($cert_id);

        return $this->responseJson($result);
    }

    public function sendExportPhytosanitaryCertificate(Request $request)
    {
        try {
            $certificate_id = $request->input('certificate_id');

            $certificate_obj = $this->agrocalidadDataService->fetchExportPhytosanitaryCertificateData($certificate_id);

            $result_response = $this->intercomCfeService->sendExportPhytosanitaryCertificateMba002($certificate_obj);

            return $result_response;
        } catch (\Exception $e) {
            return $this->responseError($e);
        }
    }

    public function sendAllExportPhytosanitaryCertificates()
    {
        $allExportPhytosanitaryCertificate002 = $this->agrocalidadDataService->fetchAllExportPhytosanitaryCertificatesData();
        foreach ($allExportPhytosanitaryCertificate002 as $exportPhytosanitaryCertificate) {
            $cetificado = $exportPhytosanitaryCertificate;
            $agrocalidadDataService = new AgrocalidadCFEDataService(new AgrocalidadDBCFERepository());
            $operatorProducts = $agrocalidadDataService->fetchCertificateProductsData($exportPhytosanitaryCertificate->id_certificado_fitosanitario);
            $cetificado->operatorProducts = $operatorProducts;
            $resultResponse = $this->intercomCfeService->sendExportPhytosanitaryCertificateMba002($cetificado);
        }


        return $resultResponse;
    }

    public function descargarXMLFitosanitario(Request $request)
    {
        $certificate_permit_id = $request->input('id_certificado_permiso');
        $codigo_fitosanitario_c = $request->input('codigo_fitosanitario_c');
        $tipo_metodo = $request->input('tipo_metodo');
        $resultResponse = $this->intercomCfeService->descargarXMLInteroperabilidad($certificate_permit_id, $codigo_fitosanitario_c, $tipo_metodo);
        return $resultResponse;
    }
}
