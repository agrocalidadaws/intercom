<?php

namespace Modules\Intercom\Domain\Xml\Interfaces;

interface XmlStrategy
{
    public function generate(): string;
}
