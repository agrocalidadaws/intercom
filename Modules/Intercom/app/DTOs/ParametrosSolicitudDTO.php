<?php

namespace Modules\Intercom\DTOs;

class ParametrosSolicitudDTO extends BaseDTO
{

    public function __construct(
        public string $idFormato,
        public string $codigoFormato,
        public string $puntoOrigen
    ) {}

    public function toArray(): array
    {
        return [
            'idFormato' => $this->idFormato,
            'codigoFormato' => $this->codigoFormato,
            'puntoOrigen' => $this->puntoOrigen
        ];
    }
}
