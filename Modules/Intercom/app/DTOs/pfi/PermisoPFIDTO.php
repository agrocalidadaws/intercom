<?php

namespace Modules\Intercom\DTOs\pfi;

use Modules\Intercom\DTOs\BaseDTO;

class PermisoPFIDTO extends BaseDTO
{

    public function __construct(
        public string $numeroPermiso,
        public int $estadoCambio,
        public string $fechaEmision,
        public int $diasVigencia,
        public string $proteccionFitosanitaria,
        public string $nombreFuncionario,
        public string $documentoFechaEmision,
        public string $referennciaOriginalEmision,
        public string $numeroFitosanitarioOriginal,
        public string $archivoAdjuntoPath,
        public int $descripcionDocumento,
        public string $lugarEmision,
        public string $medioTransporte,
        public int $modoTransporte,
        public string $nombreTrasporte,
        public string $numeroSello,
        public string $puntoOrigen,
        public string $envioRecepcionDocumento,
        public array $informacionAdicionalPFI,
        public array $envioRecepcionPFI,
        public array $paisesInterrelacionadosPFI,
        public array $productosCertificadosPFI
    ) {}

    public function toArray(): array
    {
        return [
            'numeroPermiso' => $this->numeroPermiso,
            'estadoCambio' => $this->estadoCambio,
            'fechaEmision' => $this->fechaEmision,
            'diasVigencia' => $this->diasVigencia,
            'proteccionFitosanitaria' => $this->proteccionFitosanitaria,
            'nombreFuncionario' => $this->nombreFuncionario,
            'documentoFechaEmision' => $this->documentoFechaEmision,
            'referennciaOriginalEmision' => $this->referennciaOriginalEmision,
            'numeroFitosanitarioOriginal' => $this->numeroFitosanitarioOriginal,
            'archivoAdjuntoPath' => $this->archivoAdjuntoPath,
            'descripcionDocumento' => $this->descripcionDocumento,
            'lugarEmision' => $this->lugarEmision,
            'medioTransporte' => $this->medioTransporte,
            'modoTransporte' => $this->modoTransporte,
            'nombreTrasporte' => $this->nombreTrasporte,
            'numeroSello' => $this->numeroSello,
            'puntoOrigen' => $this->puntoOrigen,
            'envioRecepcionDocumento' => $this->envioRecepcionDocumento,
            'informacionAdicionalPFI' => $this->informacionAdicionalPFI,
            'envioRecepcionPFI' => $this->envioRecepcionPFI,
            'paisesInterrelacionadosPFI' => $this->paisesInterrelacionadosPFI,
            'productosCertificadosPFI' => $this->productosCertificadosPFI
        ];
    }

}
