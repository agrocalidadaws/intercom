<?php

namespace Modules\Intercom\DTOs\cfe;

use Modules\Intercom\DTOs\BaseDTO;

class CertificadoCFEDTO extends BaseDTO
{

    public function __construct(
        public string $numeroCertificado,
        public int $nombreCertificado,
        public int $estadoCambio,
        public string $fechaEmision,
        public string $proteccionFitosanitaria,
        public string $documentoFechaEmision,
        public string $referennciaOriginalEmision,
        public string $numeroFitosanitarioOriginal,
        public string $archivoAdjuntoPath,
        public int $descripcionDocumento,
        public string $lugarEmision,
        public string $funcionarioAutorizado,
        public int $cartificacionEstandar,
        public string $medioTransporte,
        public int $modoTransporte,
        public string $nombreTrasporte,
        public string $numeroSello,
        public string $puntoOrigen,
        public string $envioRecepcionDocumento,
        public array $informacionAdicionalCFE,
        public array $envioRecepcionCFE,
        public array $paisesInterrelacionadosCFE,
        public array $productosCertificadosCFEDTO
    ) {}

    public function toArray(): array
    {
        return [
            'numeroCertificado' => $this->numeroCertificado,
            'nombreCertificado' => $this->nombreCertificado,
            'estadoCambio' => $this->estadoCambio,
            'fechaEmision' => $this->fechaEmision,
            'proteccionFitosanitaria' => $this->proteccionFitosanitaria,
            'documentoFechaEmision'=> $this->documentoFechaEmision,
            'referennciaOriginalEmision' => $this->referennciaOriginalEmision,
            'numeroFitosanitarioOriginal' => $this->numeroFitosanitarioOriginal,
            'archivoAdjuntoPath' => $this->archivoAdjuntoPath,
            'descripcionDocumento' => $this->descripcionDocumento,
            'lugarEmision' => $this->lugarEmision,
            'funcionarioAutorizado' => $this->funcionarioAutorizado,
            'cartificacionEstandar' => $this->cartificacionEstandar,
            'medioTransporte' => $this->medioTransporte,
            'modoTransporte' => $this->modoTransporte,
            'nombreTrasporte' => $this->nombreTrasporte,
            'numeroSello' => $this->numeroSello,
            'puntoOrigen' => $this->puntoOrigen,
            'envioRecepcionDocumento' => $this->envioRecepcionDocumento,
            'informacionAdicionalCFE' => $this->informacionAdicionalCFE,
            'envioRecepcionCFE' => $this->envioRecepcionCFE,
            'paisesInterrelacionadosCFE' => $this->paisesInterrelacionadosCFE,
            'productosCertificadosCFEDTO' =>$this->productosCertificadosCFEDTO
        ];
    }
    
}
