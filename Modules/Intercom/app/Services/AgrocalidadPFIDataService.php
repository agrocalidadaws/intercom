<?php

namespace Modules\Intercom\Services;

use Illuminate\Support\Facades\Log;
use Modules\Intercom\Interfaces\AgrocalidadPFIRepository;

class AgrocalidadPFIDataService
{

    public function __construct(AgrocalidadPFIRepository $repository)
    {
        $this->repository = $repository;
    }

    public function fetchAllPhytosanitaryImportPermitData(): array
    {
        try {
            $importacion = $this->repository->fetchAllPhytosanitaryImportPermit();
            return $importacion;
        } catch (\Exception $e) {
            throw new \Exception("Error class AgrocalidadPFIDataService: " . $e->getMessage());
        }
    }

    public function fetchAllPhytosanitaryImportPermitProductos($id_importacion): array
    {
        try {
            $importacionProductos = $this->repository->fetchImportPermitProducts($id_importacion);
            return $importacionProductos;
        } catch (\Exception $e) {
            throw new \Exception("Error class AgrocalidadPFIDataService: " . $e->getMessage());
        }
    }

    public function fetchAllPhytosanitaryImportPermit(): \stdClass
    {
        try {
            $importacion = $this->repository->fetchAllPhytosanitaryImportPermitOne();
            $importacionProductos = $this->repository->fetchImportPermitProducts($importacion->id_importacion);
            $importacion->importacionProductos = $importacionProductos;
            return $importacion;
        } catch (\Exception $e) {
            throw new \Exception("Error: " . $e->getMessage());
        }
    }

    public function fetchAllPhytosanitaryImportPermitId($idPermiso)
    {
        try {
            $importacion = $this->repository->fetchPhytosanitaryImportPermit($idPermiso);
           /* $importacionProductos = $this->repository->fetchImportPermitProducts($importacion->id_importacion);

            $importacion->importacionProductos = $importacionProductos;*/

            return $importacion;
        } catch (\Exception $e) {
            throw new \Exception("Error class AgrocalidadPFIDataService: " . $e->getMessage());
        }
    }
}
