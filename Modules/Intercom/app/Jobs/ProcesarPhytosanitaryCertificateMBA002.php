<?php

namespace Modules\Intercom\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\Intercom\Repositories\AgrocalidadDBCFERepository;
use Modules\Intercom\Services\AgrocalidadCFEDataService;
use Modules\Intercom\Services\IntercomCfeService;

class ProcesarPhytosanitaryCertificateMBA002 implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $message = 'OK') {}

    public function handle()
    {
        try {
            $agrocalidadDataService = new AgrocalidadCFEDataService(new AgrocalidadDBCFERepository());
            $allExportPhytosanitaryCertificate002 = $agrocalidadDataService->fetchAllExportPhytosanitaryCertificatesData();
            foreach ($allExportPhytosanitaryCertificate002 as $exportPhytosanitaryCertificate) {
                $cetificado = $exportPhytosanitaryCertificate;
                $intercomCfeService = new IntercomCfeService();
                $operatorProducts = $agrocalidadDataService->fetchCertificateProductsData($exportPhytosanitaryCertificate->id_certificado_fitosanitario);
                $cetificado->operatorProducts = $operatorProducts;
                $intercomCfeService->sendExportPhytosanitaryCertificateMba002($cetificado);
            }
        } catch (\Exception $e) {
            Log::info("Ocurrio el error class ProcesarPhytosanitaryCertificateMBA002: " . $e->getMessage() );
        }
    }
}
