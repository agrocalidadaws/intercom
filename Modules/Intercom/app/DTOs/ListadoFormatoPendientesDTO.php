<?php

namespace Modules\Intercom\DTOs;

class ListadoFormatoPendientesDTO extends BaseDTO {

    public function __construct(
      public int $idSolicitud,
      public string $idFormato,
      public string $codigoFormato,
      public string $puntoOrigen,
      public string $fechaEnvio,
      public string $estadoDocumentos,
      public string $fechaRecepcion
    ){}

    public function toArray(): array{
        return [
            'idSolicitud'=>$this->idSolicitud,
            'idFormato'=>$this->idFormato,
            'codigoFormato'=>$this->codigoFormato,
            'puntoOrigen'=>$this->puntoOrigen,
            'fechaEnvio'=>$this->fechaEnvio,
            'estadoDocumentos'=>$this->estadoDocumentos,
            'fechaRecepcion'=>$this->fechaRecepcion
        ];
    }
}