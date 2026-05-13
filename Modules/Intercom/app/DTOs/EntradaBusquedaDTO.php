<?php

namespace Modules\Intercom\DTOs;

class EntradaBusquedaDTO extends BaseDTO {

    public function __construct(
        public string $codigoFormato,
        public string $puntoDestino
    ) {}

    public function toArray(): array{
        return [
            'codigoFormato'=>$this->codigoFormato,
            'puntoDestino'=>$this->puntoDestino
        ];
    }

}