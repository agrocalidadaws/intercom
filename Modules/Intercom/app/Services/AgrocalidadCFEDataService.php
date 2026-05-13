<?php

namespace Modules\Intercom\Services;

use Illuminate\Support\Facades\Log;
use Modules\Intercom\Interfaces\AgrocalidadCFERepository;

class AgrocalidadCFEDataService
{
    public function __construct(AgrocalidadCFERepository $repository)
    {
        $this->repository = $repository;
    }

    public function fetchAllExportPhytosanitaryCertificatesData(): array
    {
        try {
            $result = $this->repository->fetchAllExportPhytosanitaryCertificates();
            return $result;
        } catch (\Exception $e) {
            throw new \Exception("Error class AgrocalidadCFEDataService: ".$e->getMessage());
        }
    }

    public function fetchCertificateProductsData(int $certificate_id): array
    {
        try {
            $operatorProducts = $this->repository->fetchCertificateProducts($certificate_id);
            return $operatorProducts;
        } catch (\Exception $e) {
           throw new \Exception("Error class AgrocalidadCFEDataService: ".$e->getMessage());
        }
    }

    public function fetchExportPhytosanitaryCertificateData(int $certificate_id): \stdClass
    {
        try {
            $certificate = $this->repository->fetchExportPhytosanitaryCertificate($certificate_id);
            $operatorProducts = $this->repository->fetchCertificateProducts($certificate_id);

            $certificate->operatorProducts = $operatorProducts;

            return  $certificate;
        } catch (\Exception $e) {
           throw new \Exception("Error class AgrocalidadCFEDataService: ".$e->getMessage());
        }
    }
}
