<?php

namespace Modules\Intercom\DTOs;

class ParametrosBusquedaDTO extends BaseDTO {

    public function __construct(
        public string $puntoOrigen,
        public String $fechaEnvioDesde,
        public String $fechaEnvioHasta,
        public int $registrosPagina,
        public int $numeroPagina
    ){}

    public function toArray(): array
    {
        return [
            'puntoOrigen' => $this->puntoOrigen,
            'fechaEnvioDesde' => $this->fechaEnvioDesde,
            'fechaEnvioHasta' => $this->fechaEnvioHasta,
            'registrosPagina' => $this->registrosPagina,
            'numeroPagina' => $this->numeroPagina
        ];
    }

}