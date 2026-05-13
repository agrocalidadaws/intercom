<?php

namespace Modules\Intercom\DTOs;

class ErroresNotificacionesResultadoEnvioDTO extends BaseDTO {

    public function __construct(
        public int $codigoError,
        public string $detalleError
    ){}

    public function toArray(): array {
        return [
           'codigoError'=>$this->codigoError,
           'detalleError'=>$this->detalleError
        ];
    }

}