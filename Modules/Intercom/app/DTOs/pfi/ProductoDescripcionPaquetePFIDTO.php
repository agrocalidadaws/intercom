<?php

namespace Modules\Intercom\DTOs\pfi;

use Modules\Intercom\DTOs\BaseDTO;

class ProductoDescripcionPaquetePFIDTO extends BaseDTO {

    public function __construct(
        public string $codigoNivelEmbalaje,
        public string $cofigoTipoPaquete,
        public int $numeroPaquetes
    ){}

    public function toArray(): array{
        return [
            'codigoNivelEmbalaje'=>$this->codigoNivelEmbalaje,
            'cofigoTipoPaquete'=>$this->cofigoTipoPaquete,
            'numeroPaquetes'=>$this->numeroPaquetes
        ];
    }

}