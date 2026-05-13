<?php

namespace Modules\Intercom\Interfaces;

interface AgrocalidadPFIRepository {

    public function fetchAllPhytosanitaryImportPermit();

    public function fetchPhytosanitaryImportPermit(int $cert_id);

    public function fetchImportPermitProducts(int $cert_id);

}