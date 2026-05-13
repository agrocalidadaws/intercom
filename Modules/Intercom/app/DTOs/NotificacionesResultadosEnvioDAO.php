<?php

namespace Modules\Intercom\DTOs;

class NotificacionesResultadosEnvioDAO extends BaseDTO {

    public function __construct(
        public string $idSolicitud,
        public string $codigoFormato,
        public string $puntoDestino,
        public string $fechaRecepcionIntercom,
        public string $fechaRecepcionDestino,
        public string $estadoDocumento,
        public int $superoCantidadIntento,
        public string $tipoDocumento,
        public array $erroresNotificacionesResultadoEnvio
    ){}

    public function toArray(): array {
        return [
            'idSolicitud'=>$this->idSolicitud,
            'codigoFormato'=>$this->codigoFormato,
            'puntoDestino'=>$this->puntoDestino,
            'fechaRecepcionIntercom'=>$this->fechaRecepcionIntercom,
            'fechaRecepcionDestino'=>$this->fechaRecepcionDestino,
            'estadoDocumento'=>$this->estadoDocumento,
            'superoCantidadIntento'=>$this->superoCantidadIntento,
            'tipoDocumento'=>$this->tipoDocumento,
            'erroresNotificacionesResultadoEnvio'=>$this->erroresNotificacionesResultadoEnvio
        ];
    }

}