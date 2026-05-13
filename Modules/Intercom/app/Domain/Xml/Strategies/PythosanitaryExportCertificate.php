<?php

namespace Modules\Intercom\Domain\Xml\Strategies;

use Modules\Intercom\Domain\Xml\Classes\CertificateAdapter;
use Modules\Intercom\Domain\Xml\Composites\XmlComposite;
use Modules\Intercom\Domain\Xml\Composites\XmlNode;
use Modules\Intercom\Domain\Xml\Interfaces\XmlStrategy;

class PythosanitaryExportCertificate implements XmlStrategy
{
    private $data;

    public function __construct($certificate_data)
    {
        $this->data = $certificate_data;
    }

    public function generate(): string
    {
        $SPSCertificate = new XmlComposite('rsm:SPSCertificate', [
            'xmlns:ds' => 'http://www.w3.org/2000/09/xmldsig#',
            'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
            'xmlns:rsm' => 'urn:un:unece:uncefact:data:standard:SPSCertificate:17',
            'xmlns:udt' => 'urn:un:unece:uncefact:data:standard:UnqualifiedDataType:21',
            'xmlns:ram' => 'urn:un:unece:uncefact:data:standard:ReusableAggregateBusinessInformationEntity:21',
            'xsi:schemaLocation' => 'urn:un:unece:uncefact:data:standard:SPSCertificate:17 ../../Certificado%20fitosanitario%20de%20exportaci%C3%B3n/Certificado%20fitosanitario%20de%20exportaci%C3%B3n%20(XSD-Validaciones)/data/standard/SPSCertificate_17p0.xsd'
        ]);

        $SPSExchangedDocument = $this->generate_exchange();
        $SPSCertificate->addChild($SPSExchangedDocument);

        $SPSConsignment = $this->generate_consignment();
        $SPSCertificate->addChild($SPSConsignment);



        $xml = $SPSCertificate->render();

        return $xml;
    }

    /**
     * @return XmlComposite
     */
    private function generate_exchange(): XmlComposite
    {
        // Crear el nodo raíz
        $spsExchangedDocument = new XmlComposite('rsm:SPSExchangedDocument');

        $data = $this->data;

        // Agregar los nodos hijos
        $spsExchangedDocument->addChild(new XmlNode('ram:ID', $data->ID));
        $spsExchangedDocument->addChild(new XmlNode('ram:TypeCode', $data->TypeCode));
        $spsExchangedDocument->addChild(new XmlNode('ram:StatusCode', $data->StatusCode));

        // Node IssueDateTime
        $issueDateTime = new XmlComposite('ram:IssueDateTime');
        $issueDateTime->addChild(new XmlNode('udt:DateTimeString', $data->IssueDateTime_DateTimeString));
        $spsExchangedDocument->addChild($issueDateTime);

        // Node IssuerSPSParty
        $issuerSPSParty = new XmlComposite('ram:IssuerSPSParty');
        $issuerSPSParty->addChild(new XmlNode('ram:Name', $data->IssuerSPSParty_Name));
        $spsExchangedDocument->addChild($issuerSPSParty);

        foreach ($data->IncludedSPSNotes as $note) {
            $includedSPSNote = new XmlComposite('ram:IncludedSPSNote');
            $includedSPSNote->addChild(new XmlNode('ram:Subject', $note['Subject']));

            if (is_array($note['Content'])) {
                $includedSPSNote->addChild(new XmlNode(
                    'ram:Content',
                    $note['Content']['_value'],
                    $note['Content']['_attributes']
                ));
            } else {
                $includedSPSNote->addChild(new XmlNode('ram:Content', $note['Content']));
            }

            $spsExchangedDocument->addChild($includedSPSNote);
        }

       
        $signatorySPSAuthentication = new XmlComposite('ram:SignatorySPSAuthentication');

        $issueSPSLocation = new XmlComposite('ram:IssueSPSLocation');
        $issueSPSLocation->addChild(new XmlNode('ram:Name', $data->IssueSPSLocation_Name));

        $providerSPSParty = new XmlComposite('ram:ProviderSPSParty');
        $specifiedSPSPerson = new XmlComposite('ram:SpecifiedSPSPerson');
        $specifiedSPSPerson->addChild(new XmlNode('ram:Name', $data->SpecifiedSPSPerson_Name));
        $providerSPSParty->addChild($specifiedSPSPerson);

        $includedSPSClause = new XmlComposite('ram:IncludedSPSClause');
        $includedSPSClause->addChild(new XmlNode('ram:ID', $data->IncludedSPSClause));

        $signatorySPSAuthentication->addChild($issueSPSLocation);
        $signatorySPSAuthentication->addChild($providerSPSParty);
        $signatorySPSAuthentication->addChild($includedSPSClause);

        $spsExchangedDocument->addChild($signatorySPSAuthentication);

        return $spsExchangedDocument;
    }

    /**
     * @return XmlComposite
     */
    private function generate_consignment(): XmlComposite
    {
        $consignment = $this->data->SPSConsignment;

        $spsConsignment = new XmlComposite('rsm:SPSConsignment');
        // Agregar el consignador
        $consignor = new XmlComposite('ram:ConsignorSPSParty');
        $consignor->addChild(new XmlNode('ram:Name', $consignment->ConsignorSPSParty_Name));
        $address = new XmlComposite('ram:SpecifiedSPSAddress');
        $address->addChild(new XmlNode('ram:LineOne', $consignment->ConsignorSPSParty_SpecifiedSPSAddress_LineOne));
        $consignor->addChild($address);
        $spsConsignment->addChild($consignor);

        // Agregar el consignatario
        $consignee = new XmlComposite('ram:ConsigneeSPSParty');
        $consignee->addChild(new XmlNode('ram:Name', $consignment->ConsigneeSPSParty_Name));
        $address = new XmlComposite('ram:SpecifiedSPSAddress');
        $address->addChild(new XmlNode('ram:LineOne', $consignment->ConsigneeSPSParty_SpecifiedSPSAddress_LineOne));
        $consignee->addChild($address);
        $spsConsignment->addChild($consignee);

        // Agregar países de exportación, importación y tránsito
        $exportSPSCountry = new XmlComposite('ram:ExportSPSCountry');
        $exportSPSCountry->addChild(new XmlNode('ram:ID', $consignment->ExportSPSCountry_ID));

        $importSPSCountry = new XmlComposite('ram:ImportSPSCountry');
        $importSPSCountry->addChild(new XmlNode('ram:ID', $consignment->ImportSPSCountry_ID));

        $transitSPSCountry = new XmlComposite('ram:TransitSPSCountry');
        $transitSPSCountry->addChild(new XmlNode('ram:ID', $consignment->TransitSPSCountry_ID));

        $spsConsignment->addChild($exportSPSCountry);
        $spsConsignment->addChild($importSPSCountry);
        $spsConsignment->addChild($transitSPSCountry);

        // Agregar base de descarga
        $unloadingBaseport = new XmlComposite('ram:UnloadingBaseportSPSLocation');
        $unloadingBaseport->addChild(new XmlNode('ram:ID', $consignment->UnloadingBaseportSPSLocation_ID));
        $unloadingBaseport->addChild(new XmlNode('ram:Name', $consignment->UnloadingBaseportSPSLocation_Name));
        $spsConsignment->addChild($unloadingBaseport);

        // Agregar evento de inspección
        $examinationEvent = new XmlComposite('ram:ExaminationSPSEvent');
        $occurrenceLocation = new XmlComposite('ram:OccurrenceSPSLocation');
        $occurrenceLocation->addChild(new XmlNode('ram:Name', 'NULL'));
        $examinationEvent->addChild($occurrenceLocation);
        $spsConsignment->addChild($examinationEvent);

        $consignmentItem = $this->generate_product_items($consignment);
        $spsConsignment->addChild($consignmentItem);

        return $spsConsignment;
    }

    /**
     * @return XmlComposite
     */
    private function generate_product_items($consignment): XmlComposite
    {
        // Agregar consignación incluida
        $consignmentItem = new XmlComposite('ram:IncludedSPSConsignmentItem');

        foreach ($consignment->IncludedSPSTradeLineItem as $item) {

            // Agregar Productos

            $tradeLineItem = new XmlComposite('ram:IncludedSPSTradeLineItem');
            $tradeLineItem->addChild(new XmlNode('ram:SequenceNumeric', $item->SequenceNumeric));
            $tradeLineItem->addChild(new XmlNode('ram:Description', $item->Description));
            $tradeLineItem->addChild(new XmlNode('ram:CommonName', $item->CommonName));
            $tradeLineItem->addChild(new XmlNode('ram:ScientificName', $item->ScientificName));
            $tradeLineItem->addChild(new XmlNode('ram:IntendedUse', $item->IntendedUse));
            $tradeLineItem->addChild(new XmlNode('ram:NetWeightMeasure', $item->NetWeightMeasure));
            $tradeLineItem->addChild(new XmlNode('ram:GrossWeightMeasure', $item->GrossWeightMeasure));
            $tradeLineItem->addChild(new XmlNode('ram:NetVolumeMeasure', $item->NetVolumeMeasure));
            $tradeLineItem->addChild(new XmlNode('ram:GrossVolumeMeasure', $item->GrossVolumeMeasure));

            $notes = [
                ['OPTND', '100 Bags of 20 Cardboard Boxes'],
                ['RPCST', '3'],
            ];
            foreach ($notes as [$subject, $content]) {
                $note = new XmlComposite('ram:AdditionalInformationSPSNote');
                $note->addChild(new XmlNode('ram:Subject', $subject));
                $note->addChild(new XmlNode('ram:Content', $content));
                $tradeLineItem->addChild($note);
            }

            $originSPSCountry = new XmlComposite('ram:OriginSPSCountry');
            $originSPSCountry->addChild(new XmlNode('ram:ID', 'EC'));
            $subordinateSPSCountrySubDivision = new XmlComposite('ram:SubordinateSPSCountrySubDivision');
            $subordinateSPSCountrySubDivision->addChild(new XmlNode('ram:Name', 'Ecuador'));
            $originSPSCountry->addChild($subordinateSPSCountrySubDivision);

            $tradeLineItem->addChild($originSPSCountry);

            $consignmentItem->addChild($tradeLineItem);
        }
        return $consignmentItem;
    }
    
}
