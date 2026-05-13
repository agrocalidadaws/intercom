<?php

namespace Modules\Intercom\Domain\Xml\Composites;

class XmlNode extends XmlElement
{
    private $value;
    private $attributes = [];

    public function __construct(string $name, string $value = '', array $attributes = [])
    {
        parent::__construct($name);
        $this->value = $value;
        $this->attributes = $attributes;
    }

    public function render(): string
    {
        $attrs = '';
        foreach ($this->attributes as $key => $value) {
            $attrs .= " $key=\"$value\"";
        }

        return "<{$this->name}{$attrs}>{$this->value}</{$this->name}>";
    }
}
