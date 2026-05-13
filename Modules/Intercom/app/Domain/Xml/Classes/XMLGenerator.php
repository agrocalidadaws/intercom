<?php

namespace Modules\Intercom\Domain\Xml\Classes;

use Modules\Intercom\Domain\Xml\Interfaces\XmlStrategy;

class XMLGenerator
{
    private XmlStrategy $xmlStrategy;

    public function setXmlStrategy(XmlStrategy $xmlStrategy): void
    {
        $this->xmlStrategy = $xmlStrategy;
    }

    public function generateXML()
    {
        $xml = $this->xmlStrategy->generate();

        return $xml;
    }
}
