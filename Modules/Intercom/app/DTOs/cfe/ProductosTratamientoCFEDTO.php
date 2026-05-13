<?php

namespace Modules\Intercom\DTOs\cfe;

use Modules\Intercom\DTOs\BaseDTO;

class ProductosTratamientoCFEDTO extends BaseDTO {

    public function __construct(
        public int $tipoCodigo,
        public string $fechaIncio,
        public string $fechaFinal,
        public int $duracion,
        public array $productosTiposTratamientosCFE
    ){}

    public function toArray(): array{
        return [
            'tipoCodigo'=>$this->tipoCodigo,
            'fechaIncio'=>$this->fechaIncio,
            'fechaFinal'=>$this->fechaFinal,
            'duracion'=>$this->duracion,
            'productosTiposTratamientosCFE'=>$this->productosTiposTratamientosCFE
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

}