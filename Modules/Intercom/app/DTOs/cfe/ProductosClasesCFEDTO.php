<?php

namespace Modules\Intercom\DTOs\cfe;

use Modules\Intercom\DTOs\BaseDTO;

class ProductosClasesCFEDTO extends BaseDTO {

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