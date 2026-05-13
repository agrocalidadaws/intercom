<?php

namespace Modules\Intercom\DTOs\pfi;

use Modules\Intercom\DTOs\BaseDTO;

class ProductosClasesPFIDTO extends BaseDTO {

    public function __construct(
        public string $systemName,
        public string $classeCodigo
    ){}

    public function toArray(): array
    {
        return [
            'systemName'=>$this->systemName,
            'classeCodigo'=>$this->classeCodigo
        ];
    }
    
}