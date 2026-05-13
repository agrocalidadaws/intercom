<?php

namespace Modules\Intercom\DTOs;

class FormatoPendientesDTO extends BaseDTO {

    public function __construct(
        public int $funcion,
        public string $tipoCodigo,
        public string $tipoFormato,
        public int $numeroPagina,
        public int $totalPagina,
        public int $totalRegistro,
        public int $tamanoPagina,
        public array $listaFormatoPendiente
    ){}

    public function toArray(): array{
        return [
            'funcion'=>$this->funcion,
            'tipoCodigo'=>$this->tipoCodigo,
            'tipoFormato' =>$this->tipoFormato,
            'numeroPagina' =>$this->numeroPagina,
            'totalPagina' =>$this->totalPagina,
            'totalRegistro' =>$this->totalRegistro,
            'tamanoPagina' =>$this->tamanoPagina,
            'listaFormatoPendiente' =>$this->listaFormatoPendiente
        ];
    }

}