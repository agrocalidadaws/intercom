<?php

namespace Modules\Intercom\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\Intercom\Repositories\AgrocalidadDBPFIRepository;
use Modules\Intercom\Services\AgrocalidadPFIDataService;
use Modules\Intercom\Services\IntercomPfiService;

class PermitePhytosanitaryImportMBA002 implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $message = 'OK') {}

    public function handle()
    {

        try {
            $agrocalidadDataService = new AgrocalidadPFIDataService(new AgrocalidadDBPFIRepository);
            $allPhytosanitaryImportPermit = $agrocalidadDataService->fetchAllPhytosanitaryImportPermitData();
           foreach ($allPhytosanitaryImportPermit as $phytosanitaryImportPermit) {
                $permiso = $phytosanitaryImportPermit;
                $intercomPfiService = new IntercomPfiService();
                $permisoProducto = $agrocalidadDataService->fetchAllPhytosanitaryImportPermitProductos($permiso->id_importacion);
                $permiso->importacionProductos = $permisoProducto;
                $intercomPfiService->sendPhytonsanitaryImportPermitMba002($permiso);
            }
        } catch (\Exception $e) {
            Log::info("Error class PermitePhytosanitaryImportMBA002: " .$e->getMessage());
        }
    }
}
