<?php

namespace Modules\Intercom\DTOs\pfi;

use Modules\Intercom\DTOs\BaseDTO;

class ProductosPersmisoPFIDTO extends BaseDTO {

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
        public string $nombrePais,
        public string $nombreZonaDentroPO,
        public array $productosInformacionAdicionalPFI,
        public array $productosClasesPFI,
        public array $productoDescripcionPaquetePFI,
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
            'nombrePais'=>$this->nombrePais,
            'nombreZonaDentroPO'=>$this->nombreZonaDentroPO,
            'productosInformacionAdicionalPFI'=>$this->productosInformacionAdicionalPFI,
            'productosClasesPFI'=>$this->productosClasesPFI,
            'productoDescripcionPaquetePFI'=>$this->productoDescripcionPaquetePFI,
        ];
    }

}