<?php

namespace Modules\Intercom\DTOs\cfe;

use Modules\Intercom\DTOs\BaseDTO;

class ProductosTiposTratamientosCFEDTO extends BaseDTO {

    public function __construct(
        public string $descripcionTraOne,
        public string $descripcionTraTwo
    ){}

    public function toArray(): array {
        return [
            'descripcionTraOne' => $this->descripcionTraOne,
            'descripcionTraTwo' => $this->descripcionTraTwo,
        ];
    }

}