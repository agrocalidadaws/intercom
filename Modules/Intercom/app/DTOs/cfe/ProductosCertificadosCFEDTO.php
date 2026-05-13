<?php

namespace Modules\Intercom\DTOs\cfe;

use Modules\Intercom\DTOs\BaseDTO;

class ProductosCertificadosCFEDTO extends BaseDTO {

    public function __construct(
        public string $descripcion,
        public string $nombreComun,
        public string $nombreCientifico,
        public string $productoIPPC,
        public float $pesoNeto,
        public float $pesoBruto,
        public float $volumenNeto,
        public float $volumenBruto,
        public string $paisOrigenId,
        public string $nombreZonaDentroPO,
        public array $productosInformacionAdicionalCFE,
        public array $productosClasesCFE,
        public array $productoDescripcionPaqueteCFE,
        public array $productosTratamientosCFE,
    ){}

    public function toArray(): array
    {
        return [
            'descripcion'=>$this->descripcion,
            'nombreComun'=>$this->nombreComun,
            'nombreCientifico'=>$this->nombreCientifico,
            'productoIPPC'=>$this->productoIPPC,
            'pesoNeto'=>$this->pesoNeto,
            'pesoBruto'=>$this->pesoBruto,
            'volumenNeto'=>$this->volumenNeto,
            'volumenBruto'=>$this->volumenBruto,
            'paisOrigenId'=>$this->paisOrigenId,
            'nombreZonaDentroPO'=>$this->nombreZonaDentroPO,
            'productosInformacionAdicionalCFE'=>$this->productosInformacionAdicionalCFE,
            'productosClasesCFE'=>$this->productosClasesCFE,
            'productoDescripcionPaqueteCFE'=>$this->productoDescripcionPaqueteCFE,
            'productosTratamientosCFE'=>$this->productosTratamientosCFE
        ];
    }

}