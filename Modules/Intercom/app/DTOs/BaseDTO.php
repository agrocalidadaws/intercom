<?php

namespace Modules\Intercom\DTOs;

class BaseDTO
{
    
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
