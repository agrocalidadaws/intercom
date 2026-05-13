<?php

namespace Modules\Intercom\Interfaces;

interface AgrocalidadCFERepository
{
    public function fetchAllExportPhytosanitaryCertificates();

    public function fetchExportPhytosanitaryCertificate(int $cert_id);

    public function fetchCertificateProducts(int $cert_id);
}
