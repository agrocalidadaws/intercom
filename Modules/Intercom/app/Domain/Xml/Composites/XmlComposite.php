<?php

namespace Modules\Intercom\Domain\Xml\Composites;

class XmlComposite extends XmlElement
{
    private $attributes = [];
    private $children = [];

    public function __construct(string $name, array $attributes = [])
    {
        parent::__construct($name);
        $this->attributes = $attributes;
    }

    public function addChild(XmlElement $child): void
    {
        $this->children[] = $child;
    }

    public function render(): string
    {
        $attrs = '';
        foreach ($this->attributes as $key => $value) {
            $attrs .= " $key=\"$value\"";
        }

        $childrenXml = '';
        foreach ($this->children as $child) {
            $childrenXml .= $child->render();
        }

        return "<{$this->name}{$attrs}>{$childrenXml}</{$this->name}>";
    }
}
