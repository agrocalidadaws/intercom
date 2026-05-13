<?php

namespace Modules\Intercom\DTOs\cfe;

use Modules\Intercom\DTOs\BaseDTO;

class ProductosInformacionAdicionalCFEDTO extends BaseDTO {

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