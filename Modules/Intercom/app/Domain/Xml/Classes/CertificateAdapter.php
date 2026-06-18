<?php

namespace Modules\Intercom\Domain\Xml\Classes;

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Date;
use Modules\Intercom\Domain\Xml\IntercomConstants;
use Modules\Intercom\DTOs\cfe\CertificadoCFEDTO;
use Modules\Intercom\DTOs\cfe\InformacionAdicionalCFEDTO;
use Modules\Intercom\DTOs\cfe\EnvioRecepcionCFEDTO;
use Modules\Intercom\DTOs\cfe\PaisesInterrelacionadosCFEDTO;
use Modules\Intercom\DTOs\cfe\ProductoDescripcionPaqueteCFEDTO;
use Modules\Intercom\DTOs\cfe\ProductosCertificadosCFEDTO;
use Modules\Intercom\DTOs\cfe\ProductosClasesCFEDTO;
use Modules\Intercom\DTOs\cfe\ProductosInformacionAdicionalCFEDTO;
use Modules\Intercom\DTOs\cfe\ProductosTiposTratamientosCFEDTO;
use Modules\Intercom\DTOs\cfe\ProductosTratamientoCFEDTO;
use Modules\Intercom\Repositories\AgrocalidadDBCFERepository;
use SimpleXMLElement;

class CertificateAdapter
{
    /**
     * El contructor realiza la declaración
     * del objeto AgrocalidadDBCFERepository que realiza
     * la consulta a la base de datos.
     */
    public function __construct()
    {
        $this->agrocalidadRepository = new AgrocalidadDBCFERepository();
    }

    /**
     * Método que lee la información obtenidad desde la base de datos
     * y lo guarda en un objeto tipo  \stdClass().
     */
    public static function transform($data)
    {
        $outputData = new \stdClass();

        $outputData->ID = self::genID($data->codigo_certificado);
        $outputData->TypeCode = '851';  //Nombre de certificado

        $certStatus = [
            'Aprobado' => '70',
            'Anulado' => '40',
        ];

        $outputData->StatusCode = $certStatus[htmlspecialchars(trim($data->estado_certificado), ENT_XML1 | ENT_QUOTES, 'UTF-8')] ?? null;
        $outputData->IssueDateTime_DateTimeString = $data->fecha_aprobacion_certificado;
        $outputData->IssuerSPSParty_Name = 'Organización de Protección Fitosanitaria de Ecuador';
        $outputData->IncludedSPSNotes = self::genIncludedSPSNotes($data);

        $outputData->IssueSPSLocation_Name = htmlspecialchars(trim($data->provincia), ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $outputData->SpecifiedSPSPerson_Name = htmlspecialchars(trim($data->nombre), ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $outputData->IncludedSPSClause = 1;

        $obj = new \stdClass();
        foreach ($data->operatorProducts as $operator) {
            $products = $operator->productos_operador->productos;
            $operatorData = $operator->productos_operador->datos_operador;
            $obj->ConsignorSPSParty_Name = htmlspecialchars(trim($operatorData->nombre_operador), ENT_XML1 | ENT_QUOTES, 'UTF-8');
            $obj->ConsignorSPSParty_SpecifiedSPSAddress_LineOne = htmlspecialchars(trim($operatorData->direccion_operador), ENT_XML1 | ENT_QUOTES, 'UTF-8');
            $obj->ConsigneeSPSParty_Name = htmlspecialchars(trim($data->nombre_consignatario), ENT_XML1 | ENT_QUOTES, 'UTF-8');
            $obj->ConsigneeSPSParty_SpecifiedSPSAddress_LineOne = htmlspecialchars(trim($data->direccion_consignatario));
            $obj->ExportSPSCountry_ID = htmlspecialchars(trim($data->codigo_pais_origen), ENT_XML1 | ENT_QUOTES, 'UTF-8');
            $obj->ImportSPSCountry_ID = htmlspecialchars(trim($data->codigo_pais_detino), ENT_XML1 | ENT_QUOTES, 'UTF-8');
            $obj->TransitSPSCountry_ID = htmlspecialchars(trim($data->codigo_pais_origen), ENT_XML1 | ENT_QUOTES, 'UTF-8');
            $obj->UnloadingBaseportSPSLocation_ID = htmlspecialchars(trim($data->codigo_pais_detino), ENT_XML1 | ENT_QUOTES, 'UTF-8');
            $obj->UnloadingBaseportSPSLocation_Name = htmlspecialchars(trim($data->nombre_puerto_destino), ENT_XML1 | ENT_QUOTES, 'UTF-8');
            $obj->OccurrenceSPSLocation_Name = 'NULL';
            break;
        }

        $IncludedSPSTradeLineItem = [];

        foreach ($data->operatorProducts as $operator) {
            $products = $operator->productos_operador->productos;
            $operatorData = $operator->productos_operador->datos_operador;

            foreach ($products as $counter => $product) {
                $agrocalidadRepository = new AgrocalidadDBCFERepository();
                $dbProduct = $agrocalidadRepository->fetchProduct($product->id_producto);

                $prodObj = new \stdClass();
                $prodObj->SequenceNumeric = $counter + 1;
                $prodObj->Description = 'Ninguno';
                $prodObj->CommonName = htmlspecialchars(trim($dbProduct->nombre_comun), ENT_XML1 | ENT_QUOTES, 'UTF-8');
                $prodObj->ScientificName = htmlspecialchars(trim($dbProduct->nombre_cientifico), ENT_XML1 | ENT_QUOTES, 'UTF-8');
                $prodObj->IntendedUse = htmlspecialchars(trim($data->uso_previsto ?? 'N/A'), ENT_XML1 | ENT_QUOTES, 'UTF-8');
                $prodObj->NetWeightMeasure = $product->peso_neto == null ? 1 : $product->peso_neto;
                $prodObj->GrossWeightMeasure = $product->peso_neto == null ? 1 : $product->peso_neto;
                $prodObj->NetVolumeMeasure = $product->peso_neto == null ? 1 : $product->peso_neto;
                $prodObj->GrossVolumeMeasure = $product->peso_neto == null ? 1 : $product->peso_neto;

                $IncludedSPSTradeLineItem[] = $prodObj;
            }
        }

        $obj->IncludedSPSTradeLineItem = $IncludedSPSTradeLineItem;

        $outputData->SPSConsignment = $obj;

        return $outputData;
    }

    /**
     * Método permite reemplazar el carácter P que se presenta en código del 
     * Certificado Fitosanitario de Exportación por EC
     */
    protected static function genID($certificateCode): ?string
    {
        return (substr($certificateCode, -1) === 'P')
            ? substr_replace($certificateCode, 'EC', -1)
            : $certificateCode;
    }

    /**
     * Metodo que permite incluir en la data para la contrucción del XML
     * la información adicional que se necesita.
     * @param $data
     * @param \stdClass $certificate
     * @return void
     */
    public static function genIncludedSPSNotes($data): array
    {
        return [
            array(
                'Subject' => 'SPSFL',
                'Content' => '5'
            ),

            array(
                'Subject' => 'ADEDL',
                'Content' => array(
                    '_attributes' => array(
                        'languageID' => 'es'
                    ),
                    '_value' => htmlspecialchars(trim($data->informacion_adicional), ENT_XML1 | ENT_QUOTES, 'UTF-8')
                )
            ),

            array(
                'Subject' => 'ADIPEDL',
                'Content' => htmlspecialchars(trim($data->codigo_certificado_importacion), ENT_XML1 | ENT_QUOTES, 'UTF-8')
            ),

            array(
                'Subject' => 'ADDIEDL',
                'Content' => htmlspecialchars(trim($data->fecha_inspeccion), ENT_XML1 | ENT_QUOTES, 'UTF-8')
            ),

            array(
                'Subject' => 'ADAOEDL',
                'Content' => 'Por la presente se certifica que las plantas, productos vegetales u otros artículos reglamentados descritos aquí se han inspeccionado y/o sometido a ensayo de acuerdo con los procedimientos oficiales adecuados y se considera que están libres de las plagas cuarentenarias especificadas por la parte contrante importadora y que cumplan los requisitos fitosanitarios vigentes de la parte contratante importadora, incluidos los relativos a las plagas no cuarentenarias reglamentadas.'
            ),

            array(
                'Subject' => 'DMCL',
                'Content' => htmlspecialchars(($data->nombre_marca))
            )
        ];
    }

    /**
     * Método que permite leer el archivo XML
     * para obtener los datos y almacenarlos en una 
     * base de datos
     */
    public static function transformGetCFEmba002($data)
    {
        $xml = new SimpleXMLElement($data);
        $xml->registerXPathNamespace('ram', 'urn:un:unece:uncefact:data:standard:ReusableAggregateBusinessInformationEntity:21');

        $documentoReferencia = $xml->xpath('//rsm:SPSExchangedDocument//ram:ReferenceSPSReferencedDocument');
        $datosDocumento = self::validarEtiquetaDocumento($documentoReferencia);
        $informacionesAdicionales = $xml->xpath('//rsm:SPSExchangedDocument//ram:IncludedSPSNote');
        $informacionList = [];
        foreach ($informacionesAdicionales as $informacionAdicional) {

            $informacionAdicionalCFEDTO = new InformacionAdicionalCFEDTO(
                (string) self::validarEtiqueta($informacionAdicional->xpath('ram:Subject')),
                (string) self::validarEtiqueta($informacionAdicional->xpath('ram:Content'))
            );
            $informacionList[] = $informacionAdicionalCFEDTO;
        }

        $envioCfeExp = $xml->xpath('//rsm:SPSConsignment//ram:ConsignorSPSParty')[0];
        $envioRecepcionCFEList = [];
        $envioRecepcionCFEDTOExp = new EnvioRecepcionCFEDTO(
            (string) self::validarEtiqueta($envioCfeExp->xpath('ram:Name')),
            (string) self::validarEtiqueta($envioCfeExp->xpath('ram:SpecifiedSPSAddress//ram:LineOne')),
            (string) self::validarEtiqueta($envioCfeExp->xpath('ram:SpecifiedSPSAddress//ram:LineTwo')),
            (string) self::validarEtiqueta($envioCfeExp->xpath('ram:SpecifiedSPSAddress//ram:LineThree')),
            (string) self::validarEtiqueta($envioCfeExp->xpath('ram:SpecifiedSPSAddress//ram:LineFour')),
            (string) self::validarEtiqueta($envioCfeExp->xpath('ram:SpecifiedSPSAddress//ram:LineFive')),
            'Exporter'
        );

        $envioRecepcionCFEList[] = $envioRecepcionCFEDTOExp;

        $envioCfeImp = $xml->xpath('//rsm:SPSConsignment//ram:ConsigneeSPSParty')[0];

        $envioRecepcionCFEDTOImp = new EnvioRecepcionCFEDTO(
            (string) self::validarEtiqueta($envioCfeImp->xpath('ram:Name')),
            (string) self::validarEtiqueta($envioCfeImp->xpath('ram:SpecifiedSPSAddress//ram:LineOne')),
            (string) self::validarEtiqueta($envioCfeImp->xpath('ram:SpecifiedSPSAddress//ram:LineTwo')),
            (string) self::validarEtiqueta($envioCfeImp->xpath('ram:SpecifiedSPSAddress//ram:LineThree')),
            (string) self::validarEtiqueta($envioCfeImp->xpath('ram:SpecifiedSPSAddress//ram:LineFour')),
            (string) self::validarEtiqueta($envioCfeImp->xpath('ram:SpecifiedSPSAddress//ram:LineFive')),
            'Importer'
        );

        $envioRecepcionCFEList[] = $envioRecepcionCFEDTOImp;

        $paisesInterrelacionadosList = [];

        $paisesInterrelacionadosCFEEmpo = new PaisesInterrelacionadosCFEDTO(
            self::validarEtiqueta($xml->xpath('//rsm:SPSConsignment//ram:ExportSPSCountry//ram:ID')),
            '',
            'Export'
        );

        $paisesInterrelacionadosList[] = $paisesInterrelacionadosCFEEmpo;

        $paisesInterrelacionadosCFEImpo = new PaisesInterrelacionadosCFEDTO(
            self::validarEtiqueta($xml->xpath('//rsm:SPSConsignment//ram:ImportSPSCountry//ram:ID')),
            '',
            'Import'
        );

        $paisesInterrelacionadosList[] = $paisesInterrelacionadosCFEImpo;

        $paisesInterrelacionadosCFETran = new PaisesInterrelacionadosCFEDTO(
            self::validarEtiqueta($xml->xpath('//rsm:SPSConsignment//ram:TransitSPSCountry//ram:ID')),
            '',
            'Transit'
        );

        $paisesInterrelacionadosList[] = $paisesInterrelacionadosCFETran;

        $paisesInterrelacionadosCFEUnload = new PaisesInterrelacionadosCFEDTO(
            self::validarEtiqueta($xml->xpath('//rsm:SPSConsignment//ram:UnloadingBaseportSPSLocation//ram:ID')),
            $xml->xpath('//rsm:SPSConsignment//ram:UnloadingBaseportSPSLocation//ram:Name')[0] ?? null,
            'UnloadingBaseport'
        );

        $paisesInterrelacionadosList[] = $paisesInterrelacionadosCFEUnload;

        $productosCertificados = $xml->xpath('//rsm:SPSConsignment//ram:IncludedSPSConsignmentItem//ram:IncludedSPSTradeLineItem');
        $productosCertificadosList = [];

        foreach ($productosCertificados as $productoCertificado) {

            $additionalInformationSPSNote = $productoCertificado->xpath('ram:AdditionalInformationSPSNote');

            $productosInformacionAdicionalList = [];
            foreach ($additionalInformationSPSNote as $additionformationNote) {
                $poductosInformacionAdicionalCFEDTO = new ProductosInformacionAdicionalCFEDTO(
                    (string) self::validarEtiqueta($additionformationNote->xpath('ram:Subject')),
                    (string) self::validarEtiqueta($additionformationNote->xpath('ram:Content'))
                );
                $productosInformacionAdicionalList[] = $poductosInformacionAdicionalCFEDTO;
            }

            $applicableSPSClassification = $productoCertificado->xpath('ram:ApplicableSPSClassification');

            $applicableSPSClassificationList = [];
            foreach ($applicableSPSClassification as $applicableClassification) {

                $classCode = $applicableClassification->xpath('ram:ClassCode');
                if (empty($classCode)) {
                    $classCode = $applicableClassification->xpath('ram:ClassName');
                }

                $productosClasesCFEDTO = new ProductosClasesCFEDTO(
                    (string) self::validarEtiqueta($applicableClassification->xpath('ram:SystemName')),
                    (string) $classCode[0]
                );
                $applicableSPSClassificationList[] = $productosClasesCFEDTO;
            }

            $physicalSPSPackage = $productoCertificado->xpath('ram:PhysicalSPSPackage');

            $physicalSPSPackageList = [];
            foreach ($physicalSPSPackage as $physicalPackage) {
                $productoDescripcionPaqueteCFEDTO = new ProductoDescripcionPaqueteCFEDTO(
                    (string) self::validarEtiqueta($physicalPackage->xpath('ram:LevelCode')),
                    (string) self::validarEtiqueta($physicalPackage->xpath('ram:TypeCode')),
                    (int) self::validarEtiqueta($physicalPackage->xpath('ram:ItemQuantity'))
                );
                $physicalSPSPackageList[] = $productoDescripcionPaqueteCFEDTO;
            }

            $appliedSPSProcess = $productoCertificado->xpath('ram:AppliedSPSProcess');

            $appliedSPSProcessList = [];
            foreach ($appliedSPSProcess as $applieProcess) {


                $applicableSPSProcessCharacteristic = $applieProcess->xpath('ram:ApplicableSPSProcessCharacteristic');

                $applicableSPSProcessCharacteristicList = [];

                foreach ($applicableSPSProcessCharacteristic as $applicableProcessCharacteristic) {
                    $descripcionTraTwo = $applicableProcessCharacteristic->xpath('ram:Description')[1] ?? null;

                    if (empty($descripcionTraTwo)) {
                        $descripcionTraTwo = $applicableProcessCharacteristic->xpath('ram:ValueMeasure')[0] ?? null;
                    }
                    $productosTiposTratamientosCFEDTO = new ProductosTiposTratamientosCFEDTO(
                        (string) self::validarEtiqueta($applicableProcessCharacteristic->xpath('ram:Description')),
                        (string) $descripcionTraTwo[0]
                    );
                    $applicableSPSProcessCharacteristicList[] = $productosTiposTratamientosCFEDTO;
                }

                $productosTratamientoCFEDTO = new ProductosTratamientoCFEDTO(
                    (int) $applieProcess->xpath('ram:TypeCode')[0],
                    self::validarEtiquetaFecha($applieProcess->xpath('ram:CompletionSPSPeriod//ram:StartDateTime//udt:DateTimeString')),
                    self::validarEtiquetaFecha($applieProcess->xpath('ram:CompletionSPSPeriod//ram:EndDateTime//udt:DateTimeString')),
                    (int) self::validarEtiqueta($applieProcess->xpath('ram:CompletionSPSPeriod//ram:DurationMeasure ')),
                    $applicableSPSProcessCharacteristicList
                );
                $appliedSPSProcessList[] = $productosTratamientoCFEDTO;
            }

            $productosCertificadosCFEDTO = new ProductosCertificadosCFEDTO(
                (string) self::validarEtiqueta($productoCertificado->xpath('ram:Description')),
                (string) self::validarEtiqueta($productoCertificado->xpath('ram:CommonName')),
                (string) self::validarEtiqueta($productoCertificado->xpath('ram:ScientificName')),
                (string) self::validarEtiqueta($productoCertificado->xpath('ram:IntendedUse')),
                (float) self::validarEtiqueta($productoCertificado->xpath('ram:NetWeightMeasure')),
                (float) self::validarEtiqueta($productoCertificado->xpath('ram:GrossWeightMeasure')),
                (float) self::validarEtiqueta($productoCertificado->xpath('ram:NetVolumeMeasure')),
                (float) self::validarEtiqueta($productoCertificado->xpath('ram:GrossVolumeMeasure')),
                (string) self::validarEtiqueta($productoCertificado->xpath('ram:OriginSPSCountry//ram:ID')),
                (string) self::validarEtiqueta($productoCertificado->xpath('ram:OriginSPSCountry//ram:SubordinateSPSCountrySubDivision//ram:Name')),
                $productosInformacionAdicionalList,
                $applicableSPSClassificationList,
                $physicalSPSPackageList,
                $appliedSPSProcessList
            );

            $productosCertificadosList[] = $productosCertificadosCFEDTO;
        }

        $certificadoCFEDTO = new CertificadoCFEDTO(
            numeroCertificado: (string)self::validarEtiqueta($xml->xpath('//rsm:SPSExchangedDocument//ram:ID')),
            nombreCertificado: (int) self::validarEtiqueta($xml->xpath('//rsm:SPSExchangedDocument//ram:TypeCode')),
            estadoCambio: (int)self::validarEtiqueta($xml->xpath('//rsm:SPSExchangedDocument//ram:StatusCode')),
            fechaEmision: self::validarEtiqueta($xml->xpath('//rsm:SPSExchangedDocument//ram:IssueDateTime//udt:DateTimeString')),
            proteccionFitosanitaria: (string) self::validarEtiqueta($xml->xpath('//rsm:SPSExchangedDocument//ram:IssuerSPSParty//ram:Name')),
            documentoFechaEmision: $datosDocumento['fechaEmision'],
            referennciaOriginalEmision: $datosDocumento['referenciaOriginal'],
            numeroFitosanitarioOriginal: $datosDocumento['numeroCertificado'],
            archivoAdjuntoPath: $datosDocumento['pathDocumento'],
            descripcionDocumento: (int) $datosDocumento['descripcionDocumento'],
            lugarEmision: (string) self::validarEtiqueta($xml->xpath('//rsm:SPSExchangedDocument//ram:SignatorySPSAuthentication//ram:IssueSPSLocation//ram:Name')),
            funcionarioAutorizado: (string) self::validarEtiqueta($xml->xpath('//rsm:SPSExchangedDocument//ram:SignatorySPSAuthentication//ram:ProviderSPSParty//ram:SpecifiedSPSPerson//ram:Name')),
            cartificacionEstandar: (int)self::validarEtiqueta($xml->xpath('//rsm:SPSExchangedDocument//ram:SignatorySPSAuthentication//ram:IncludedSPSClause//ram:ID')),
            medioTransporte: (string) self::validarEtiqueta($xml->xpath('//rsm:SPSConsignment//ram:MainCarriageSPSTransportMovement//ram:ID')),
            modoTransporte: (int) self::validarEtiqueta($xml->xpath('//rsm:SPSConsignment//ram:MainCarriageSPSTransportMovement//ram:ModeCode')),
            nombreTrasporte: (string) self::validarEtiqueta($xml->xpath('//rsm:SPSConsignment//ram:MainCarriageSPSTransportMovement//ram:UsedSPSTransportMeans//ram:Name')),
            numeroSello: (string) self::validarEtiqueta($xml->xpath('//rsm:SPSConsignment//ram:UtilizedSPSTransportEquipment//ram:AffixedSPSSeal//ram:ID')),
            puntoOrigen: 0,
            envioRecepcionDocumento: '',
            informacionAdicionalCFE: $informacionList,
            envioRecepcionCFE: $envioRecepcionCFEList,
            paisesInterrelacionadosCFE: $paisesInterrelacionadosList,
            productosCertificadosCFEDTO: $productosCertificadosList
        );

        return $certificadoCFEDTO;
    }

    /**
     * Metodo que permite validad la existencia de la etiqueta
     */
    protected static function validarEtiqueta($etiqueta)
    {
        if (!empty($etiqueta)) {
            return $etiqueta[0] ?? null;
        }

        return '';
    }

    /**
     * Las viene en los XML los transforma en 
     * en formato tipo Date
     */
    protected static function validarEtiquetaFecha($etiqueta)
    {
        if (!empty($etiqueta)) {
            $dateFecha = Date::parse((string) $etiqueta[0]);
            return $dateFecha->format('Y-m-d H:i:d');
        }
        $fecha_actual = Carbon::now()->format('Y-m-d H:i:s');
        return $fecha_actual;
    }

    /**
     * Metodo que permite validad la existencia de un documeto
     * En el XML
     * En el caso de existir un documento procede a alcenar
     * En una ruta especifica
     */
    protected static function validarEtiquetaDocumento($etiqueta)
    {
        if (!empty($etiqueta)) {
            $certificadoFechaEmision = (string) self::validarEtiqueta($etiqueta[0]->xpath('ram:IssueDateTime//udt:DateTimeString'));
            $dateCertificadoFechaEmision = Date::parse($certificadoFechaEmision);
            $numeroFitosanitarioOriginal = self::validarEtiqueta($etiqueta[0]->xpath('ram:ID'));
            $contenidoPdf = base64_decode(self::validarEtiqueta($etiqueta[0]->xpath('ram:AttachmentBinaryObject')));

            Storage::makeDirectory(IntercomConstants::PATH_STORE_CFE_XML);
            Storage::disk('agrocalidad')->put(IntercomConstants::PATH_STORE_CFE_PDF . '/CFE_' . $numeroFitosanitarioOriginal . '.pdf', $contenidoPdf);

            return [
                'fechaEmision' => $dateCertificadoFechaEmision->format('Y-m-d H:i:d'),
                'referenciaOriginal' => self::validarEtiqueta($etiqueta[0]->xpath('ram:RelationshipTypeCode')),
                'numeroCertificado' => $numeroFitosanitarioOriginal,
                'pathDocumento' => IntercomConstants::PATH_STORE_CFE_PDF . '/CFE_' . $numeroFitosanitarioOriginal . '.PDF',
                'descripcionDocumento' => self::validarEtiqueta($etiqueta[0]->xpath('ram:Information'))
            ];
        }
        return [
            'fechaEmision' => Carbon::now()->format('Y-m-d H:i:s'),
            'referenciaOriginal' => '',
            'numeroCertificado' => '',
            'pathDocumento' => '',
            'descripcionDocumento' => 0
        ];
    }
}
