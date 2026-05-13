<?php

namespace Modules\Intercom\DTOs;

class ParametrosRecepcionDocumentoDTO extends BaseDTO {

    public function __construct(
        public string $codigoFormato,
        public string $recibidoConExito
    ){}

    public function toArray(): array {
        return [
            'codigoFormato' => $this->codigoFormato,
            'recibidoConExito' => $this->recibidoConExito
        ];
    }
}