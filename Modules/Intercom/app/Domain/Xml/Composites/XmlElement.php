<?php

namespace Modules\Intercom\Domain\Xml\Composites;

abstract class XmlElement
{
    protected $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    abstract public function render(): string;
}
