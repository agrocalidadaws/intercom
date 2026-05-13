<?php

namespace Modules\Intercom\DTOs;

class ErroresDocumentosRecibidosDTO extends BaseDTO {

    public function __construct(
        public string $codigoFormato,
        public string $fechaEmision,
        public string $puntoOrigen,
        public string $errorPresentado,
        public string $tipoDocumento,
        public string $estadoEnvio
    ){}

    public function toArray(): array{
        return [
            'codigoFormato'=>$this->codigoFormato,
            'fechaEmision'=>$this->fechaEmision,
            'puntoOrigen'=>$this->puntoOrigen,
            'errorPresentado'=>$this->errorPresentado,
            'tipoDocumento'=>$this->tipoDocumento,
            'estadoEnvio'=>$this->estadoEnvio
        ];
    }

}