<?php

namespace Modules\Intercom\DTOs\pfi;

use Modules\Intercom\DTOs\BaseDTO;

class PaisesInterrelacionadosPFIDTO extends BaseDTO {
    
    public function __construct(
        public string $idPais,
        public string $nombre,
        public string $tipo
    ){} 

    public function toArray(): array
    {
        return [
            'idPais'=>$this->idPais,
            'nombre'=>$this->nombre,
            'tipo'=>$this->tipo];
    }

}