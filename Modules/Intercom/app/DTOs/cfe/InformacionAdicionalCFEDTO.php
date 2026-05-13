<?php

namespace Modules\Intercom\DTOs\cfe;

use Modules\Intercom\DTOs\BaseDTO;

class InformacionAdicionalCFEDTO extends BaseDTO {

    public function __construct(
        public string $subject,
        public string $contenido
    ){}

    public function toArray(): array
    {
        return [
            'subject'=>$this->subject,
            'contenido'=>$this->contenido
        ];
    }

}