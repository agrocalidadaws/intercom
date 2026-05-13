<?php

namespace Modules\Intercom\Domain\Xml\Classes;

use Modules\Intercom\Domain\Xml\IntercomConstants;
use SimpleXMLElement;

class XmlValidator
{
    public const CFEMBA002 = "schemas/CFE/cfe-xsd/data/standard/SPSCertificate_17p0.xsd";

    public static function validate($xml, $xsdPath): bool
    {
        $xsdSchema = realpath(storage_path($xsdPath));

        if (!file_exists($xsdSchema)) {
            throw new \Exception("XSD file not found at path: $xsdPath");
        }

        $dom = new \DOMDocument();

        if (!$dom->loadXML($xml)) {
            throw new \Exception("Invalid XML format.");
        }

        libxml_use_internal_errors(true);
        $isValid = $dom->schemaValidate($xsdSchema);

        if (!$isValid) {
            $errors = libxml_get_errors();
            libxml_clear_errors();
            $mensajesError = [];
            foreach ($errors as $error) {
                $mensajesError[] = trim($error->message);
            }
            throw new \Exception('Error: ' . implode(", ", $mensajesError));
        }

        return true;
    }

    public static function validateData($data)
    {
        try {
            $xml = new SimpleXMLElement($data);
            $xml->registerXPathNamespace('ram', 'urn:un:unece:uncefact:data:standard:ReusableAggregateBusinessInformationEntity:21');
            if (empty($xml->xpath('//rsm:SPSExchangedDocument//ram:TypeCode'))) {
                throw new \Exception("Error: El TAG SPSExchangedDocument:ram:TypeCode no encontrada");
            }
            $typeCode = (int) ($xml->xpath('//rsm:SPSExchangedDocument//ram:TypeCode'))[0];

            $tipoCodigo = array_column(IntercomConstants::CAN_IPPC, 'code');
            
            if (!in_array($typeCode, $tipoCodigo)) {
                throw new \Exception('Error: El TAG SPSExchangedDocument:ram:TypeCode no termina en '. json_encode($tipoCodigo));
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
