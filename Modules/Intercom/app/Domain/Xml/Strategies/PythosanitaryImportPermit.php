<?php

namespace Modules\Intercom\Domain\Xml\Strategies;

use League\CommonMark\Util\Xml;
use Modules\Intercom\Domain\Xml\Composites\XmlComposite;
use Modules\Intercom\Domain\Xml\Composites\XmlNode;
use Modules\Intercom\Domain\Xml\Interfaces\XmlStrategy;

class PythosanitaryImportPermit implements XmlStrategy {

    private $data;

    public function __construct($permit_data)
    {
        $this->data = $permit_data;
    }
    
    public function generate(): string {
        $SPSImportPermit = new XmlComposite('rsm:SPSImportPermit', [
            'xmlns:ds' => 'http://www.w3.org/2000/09/xmldsig#',
            'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
            'xmlns:rsm' => 'urn:sgcan:intercom:data:standard:SPSImportPermit:1',
            'xmlns:udt' => 'urn:un:unece:uncefact:data:standard:UnqualifiedDataType:21',
            'xmlns:ram' => 'urn:sgcan:intercom:data:standard:INTERCOMSPSImportPermitReusableAggregateBusinessInformationEntity:1'
        ]);

        $SPSExchangedDocument = $this->generate_exchange();
        $SPSConsignment = $this->generate_consignmant();
        $SPSImportPermit->addChild($SPSExchangedDocument);
        $SPSImportPermit->addChild($SPSConsignment);

        $xml = $SPSImportPermit->render();

        return $xml;
    }

     /**
     * @return XmlComposite
     */
    private function generate_exchange(): XmlComposite
    {
        $spsExchangedDocument = new XmlComposite('rsm:SPSExchangedDocument');

        $data = $this->data;
        $spsExchangedDocument->addChild(new XmlNode('ram:ID', $data->ID));
        $spsExchangedDocument->addChild(new XmlNode('ram:StatusCode', $data->StatusCode));

        $issueDateTime = new XmlComposite('ram:IssueDateTime');
        $issueDateTime->addChild(new XmlNode('udt:DateTimeString', $data->fechaCambio));
        $spsExchangedDocument->addChild($issueDateTime);

        $effectiveDateTime = new XmlComposite('ram:EffectiveDateTime');
        $effectiveDateTime->addChild(new XmlNode('udt:DateTimeString', $data->diasVigencia, array('format'=>'804')));
        $spsExchangedDocument->addChild($effectiveDateTime);

        $issuerSPSParty = new XmlComposite('ram:IssuerSPSParty');
        $issuerSPSParty->addChild(new XmlNode('ram:Name', $data->issuerSPSParty_Name));
        $specifiedSPSPerson = new XmlComposite('ram:SpecifiedSPSPerson');
        $specifiedSPSPerson->addChild(new XmlNode('ram:Name', $data->SpecifiedSPSPerson_Name));
        $issuerSPSParty->addChild($specifiedSPSPerson);
        $spsExchangedDocument->addChild($issuerSPSParty);

        $includedSPSNote = new XmlComposite('ram:IncludedSPSNote');
        $includedSPSNote->addChild(new XmlNode('ram:Subject', $data->includedSPSNoteSubject));
        $includedSPSNote->addChild(new XmlNode('ram:Content', $data->includedSPSNoteContent));
        $spsExchangedDocument->addChild($includedSPSNote);

        $signatorySPSAuthentication = new XmlComposite('ram:SignatorySPSAuthentication');
        $issueSPSLocation = new XmlComposite('ram:IssueSPSLocation');
        $issueSPSLocation->addChild(new XmlNode('ram:Name', $data->signatorySPSAuthenticationLocation));
        $signatorySPSAuthentication->addChild( $issueSPSLocation);
        $spsExchangedDocument->addChild($signatorySPSAuthentication);

        return $spsExchangedDocument;
    }

    private function generate_consignmant(): XmlComposite
    {
        $SPSConsignment = new XmlComposite('rsm:SPSConsignment');

        $data = $this->data;

        $consigneeSPSParty = new XmlComposite('ram:ConsigneeSPSParty');
        $consigneeSPSParty -> addChild(new XmlNode('ram:Name', $data->consigneeSPSParty));
        $consigneeSpecifiedSPSAddress = new XmlComposite('ram:SpecifiedSPSAddress');
        $consigneeSpecifiedSPSAddress -> addChild(new XmlNode('ram:LineOne', $data->consigneeAddressLineOne));
        $consigneeSpecifiedSPSAddress -> addChild(new XmlNode('ram:LineTwo', $data->consigneeAddressLineTwo));
        $consigneeSpecifiedSPSAddress -> addChild(new XmlNode('ram:LineThree', $data->consigneeAddressLineThree));
        $consigneeSpecifiedSPSAddress -> addChild(new XmlNode('ram:LineFour', $data->consigneeAddressLineFour));
        $consigneeSpecifiedSPSAddress -> addChild(new XmlNode('ram:LineFive', $data->consigneeAddressLineFive));
        $consigneeSPSParty->addChild($consigneeSpecifiedSPSAddress);
        $SPSConsignment->addChild($consigneeSPSParty);

        $utilizedSPSTransportEquipment = new XmlComposite('ram:UtilizedSPSTransportEquipment');
        $affixedSPSSeal = new XmlComposite('ram:AffixedSPSSeal');
        $affixedSPSSeal->addChild(new XmlNode('ram:ID', $data->numeroSello));
        $utilizedSPSTransportEquipment->addChild($affixedSPSSeal);
        $SPSConsignment->addChild($utilizedSPSTransportEquipment);

        $consignorSPSParty = new XmlComposite('ram:ConsignorSPSParty');
        $consignorSPSParty -> addChild(new XmlNode('ram:Name', $data->consignorSPSParty));
        $consignorSpecifiedSPSAddress = new XmlComposite('ram:SpecifiedSPSAddress');
        $consignorSpecifiedSPSAddress->addChild(new XmlNode('ram:LineOne', $data->consignorAddressLineOne));
        $consignorSpecifiedSPSAddress->addChild(new XmlNode('ram:LineTwo', $data->consignorAddressLineTwo));
        $consignorSPSParty -> addChild($consignorSpecifiedSPSAddress);
        $SPSConsignment->addChild($consignorSPSParty);

        $unloadingBaseportSPSLocation = new XmlComposite('ram:UnloadingBaseportSPSLocation');
        $unloadingBaseportSPSLocation -> addChild(new XmlNode('ram:ID',$data->unloadingBaseportID));
        $unloadingBaseportSPSLocation -> addChild(new XmlNode('ram:Name',$data->unloadingBaseportName));
        $SPSConsignment->addChild($unloadingBaseportSPSLocation); 

        $loadingBaseportSPSLocation = new XmlComposite('ram:LoadingBaseportSPSLocation');
        $loadingBaseportSPSLocation -> addChild(new XmlNode('ram:ID',$data->loadingBaseportID));
        $loadingBaseportSPSLocation -> addChild(new XmlNode('ram:Name',$data->loadingBaseportName));

        $SPSConsignment->addChild($loadingBaseportSPSLocation);

        $SPSConsignment->addChild($this->generate_consignment());

        return $SPSConsignment;
    }

    /**
     * @return XmlComposite
     */
    private function generate_consignment(): XmlComposite
    {
        
        $includeConsignmentItem = new XmlComposite('ram:IncludedSPSConsignmentItem');
        foreach ($this->data->includedSPSConsignmentItem as $consignmentItem) {
            $tradeLineItem = new XmlComposite('ram:IncludedSPSTradeLineItem');

            $tradeLineItem-> addChild(new XmlNode('ram:Description',$consignmentItem->descripcion));
            $tradeLineItem-> addChild(new XmlNode('ram:CommonName',$consignmentItem->commonName));
            $tradeLineItem-> addChild(new XmlNode('ram:ScientificName',$consignmentItem->scientificNam));
            $tradeLineItem-> addChild(new XmlNode('ram:IntendedUse',$consignmentItem->intendedUse));

            $tradeLineItem-> addChild(new XmlNode('ram:NetWeightMeasure',$consignmentItem->netWeightMeasure));
            $tradeLineItem-> addChild(new XmlNode('ram:GrossWeightMeasure',$consignmentItem->grossWeightMeasure));
            $tradeLineItem-> addChild(new XmlNode('ram:NetVolumeMeasure',$consignmentItem->netVolumeMeasure));
            $tradeLineItem-> addChild(new XmlNode('ram:GrossVolumeMeasure',$consignmentItem->grossVolumeMeasure));

            $notes = [
                ['OPTND', '100 Bags of 20 Cardboard Boxes'],
                ['OQV', '8'],
                ['OQU', 'Packets'],
                ['', 'Cumple con los requisitos fitosanitarios de importación'],
                ['', 'Observaciones dentro del documento de importación']
            ];
            foreach ($notes as [$subject, $content]) {
                $note = new XmlComposite('ram:AdditionalInformationSPSNote');
                if ($subject != '') {
                    $note->addChild(new XmlNode('ram:Subject', $subject));
                }
                $note->addChild(new XmlNode('ram:Content', $content));
                $tradeLineItem->addChild($note);
            }

            $classifications = [
                ['HS', '100590'],
                ['IPPCPCVP', 'Grains'],
                ['IPPCPCC', 'Dry']
            ];

            foreach ($classifications as [$systemName, $className]) {
                $classification = new XmlComposite('ram:ApplicableSPSClassification');
                $classification->addChild(new XmlNode('ram:SystemName', $systemName));
                $classification->addChild(new XmlNode('ram:ClassName', $className));
                $tradeLineItem->addChild($classification);
            }

            $descripcionPaquete = [['0', '43', '100']];
            foreach($descripcionPaquete as [$codigoNivel, $tipoPaquete, $paquetes]) {
                $paquete = new XmlComposite('ram:PhysicalSPSPackage');
                $paquete->addChild(new XmlNode('ram:LevelCode', $codigoNivel));
                $paquete->addChild(new XmlNode('ram:TypeCode', $tipoPaquete));
                $paquete->addChild(new XmlNode('ram:ItemQuantity', $paquetes));
                $tradeLineItem->addChild($paquete);
            }

            $includeConsignmentItem->addChild($tradeLineItem);
        }

        return $includeConsignmentItem;
    }
}