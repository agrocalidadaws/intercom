<?php

namespace Modules\Intercom\DTOs\pfi;

use Modules\Intercom\DTOs\BaseDTO;

class ProductosInformacionAdicionalPFIDTO extends BaseDTO {

    public function __construct(
        public string $pinfaSubject,
        public string $pinfaContenido
    ){}

    public function toArray(): array {
        return [
            'pinfaSubject'=>$this->pinfaSubject,
            'pinfaContenido'=>$this->pinfaContenido
        ];
    }

}