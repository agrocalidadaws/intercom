<?php

namespace Modules\Intercom\DTOs\cfe;

use Modules\Intercom\DTOs\BaseDTO;

class EnvioRecepcionCFEDTO extends BaseDTO {

    public function __construct(
        public string $nombreCosigna,
        public string $direccionLineOne,
        public string $direccionLineTwo,
        public string $direccionLineThree,
        public string $direccionLineFour,
        public string $direccionLineFive,
        public string $tipo
    ){}

    public function toArray(): array{
        return [
            'nombreCosigna' => $this->nombreCosigna,
            'direccionLineOne' => $this->direccionLineOne,
            'direccionLineTwo' => $this->direccionLineTwo,
            'direccionLineThree' => $this->direccionLineThree,
            'direccionLineFour' => $this->direccionLineFour,
            'direccionLineFive' => $this->direccionLineFive,
            'tipo' => $this->tipo
        ];
    }

}