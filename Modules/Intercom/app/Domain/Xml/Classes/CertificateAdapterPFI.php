<?php

namespace Modules\Intercom\Domain\Xml\Classes;

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Date;
use Modules\Intercom\Domain\Xml\IntercomConstants;
use Modules\Intercom\DTOs\pfi\EnvioRecepcionPFIDTO;
use Modules\Intercom\DTOs\pfi\InformacionAdicionalPFIDTO;
use Modules\Intercom\DTOs\pfi\PaisesInterrelacionadosPFIDTO;
use Modules\Intercom\DTOs\pfi\PermisoPFIDTO;
use Modules\Intercom\DTOs\pfi\ProductoDescripcionPaquetePFIDTO;
use Modules\Intercom\DTOs\pfi\ProductosClasesPFIDTO;
use Modules\Intercom\DTOs\pfi\ProductosInformacionAdicionalPFIDTO;
use Modules\Intercom\DTOs\pfi\ProductosPersmisoPFIDTO;
use SimpleXMLElement;

class CertificateAdapterPFI
{

    public function __construct() {}

    /**
     * Método que lee la información obtenidad desde la base de datos
     * y lo guarda en un objeto tipo \stdClass().
     */
    public static function transform($data)
    {

        $outputData = new \stdClass();

        $outputData->ID = self::genID($data->id_vue);

        $certStatus = [
            'aprobado' => '70',
            'anulado' => '40',
        ];
        $outputData->StatusCode = $certStatus[htmlspecialchars(trim($data->estado))] ?? null;
        $outputData->fechaCambio = $data->fecha_inicio;
        $outputData->diasVigencia = $data->dias_vigencia;
        $outputData->issuerSPSParty_Name = 'Organización de Protección Fitosanitaria de Ecuador';

        $outputData->includedSPSNoteSubject = 'DMCL';
        $outputData->includedSPSNoteContent = htmlspecialchars(trim($data->nombre_exportador));

        $outputData->consigneeSPSParty = htmlspecialchars(trim($data->nombre_consorcio));
        $outputData->consigneeAddressLineOne = htmlspecialchars(trim($data->direccion_consorcio));
        $outputData->consigneeAddressLineTwo = htmlspecialchars(trim($data->parroquia_consorcio));
        $outputData->consigneeAddressLineThree = htmlspecialchars(trim($data->canton_consorcio));
        $outputData->consigneeAddressLineFour = 'Ecuador';
        $outputData->consigneeAddressLineFive = htmlspecialchars(trim($data->provincia_consorcio));

        $outputData->signatorySPSAuthenticationLocation = htmlspecialchars(trim($data->canton_consorcio));

        $outputData->numeroSello = 'G2382564';

        $outputData->consignorSPSParty = htmlspecialchars(trim($data->nombre_exportador));
        $outputData->consignorAddressLineOne = htmlspecialchars(trim($data->direccion_exportador));
        $outputData->consignorAddressLineTwo = htmlspecialchars(trim($data->pais_exportacion));

        $outputData->unloadingBaseportID = htmlspecialchars(trim($data->codigo_puerto_destino));
        $outputData->unloadingBaseportName = htmlspecialchars(trim($data->nombre_puerto_destion));

        $outputData->loadingBaseportID =  htmlspecialchars(trim($data->codigo_puerto_embarque));
        $outputData->loadingBaseportName = htmlspecialchars(trim($data->nombre_puerto_embarque));
        
        $outputData->SpecifiedSPSPerson_Name = htmlspecialchars(trim($data->nombre_tecnico), ENT_XML1 | ENT_QUOTES, 'UTF-8');

        $includedSPSConsignmentItem = [];

        foreach ($data->importacionProductos as $importacionProducto) {
            $obj = new \stdClass();

            $obj->descripcion = htmlspecialchars(trim($importacionProducto->nombre_producto_vue));
            $obj->commonName = htmlspecialchars(trim($importacionProducto->nombre_comun));
            $obj->scientificNam = htmlspecialchars(trim($importacionProducto->nombre_cientifico));
            $obj->intendedUse = 'Ninguno';
            $obj->netWeightMeasure = $importacionProducto->peso;
            $obj->grossWeightMeasure = $importacionProducto->peso;
            $obj->netVolumeMeasure = $importacionProducto->peso;
            $obj->grossVolumeMeasure = $importacionProducto->peso;
            $includedSPSConsignmentItem[] = $obj;
        }

        $outputData->includedSPSConsignmentItem = $includedSPSConsignmentItem;
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
     * Método que permite leer el archivo XML
     * para obtener los datos y almacenarlos en una 
     * base de datos
     */
    public static function transformGetPFImba002($data)
    {
        $xml = new SimpleXMLElement($data);
        $xml->registerXPathNamespace('ram', 'urn:sgcan:intercom:data:standard:INTERCOMSPSImportPermitReusableAggregateBusinessInformationEntity:1');
        
        $documentoReferencia = $xml->xpath('//rsm:SPSExchangedDocument//ram:ReferenceSPSReferencedDocument');
        $datosDocumento = self::validarEtiquetaDocumento($documentoReferencia);

        $informacionesAdicionales = $xml->xpath('//rsm:SPSExchangedDocument//ram:IncludedSPSNote');
        $informacionList = [];
        foreach ($informacionesAdicionales as $informacionAdicional) {

            $informacionAdicionalPFIDTO = new InformacionAdicionalPFIDTO(
                (string) self::validarEtiqueta($informacionAdicional->xpath('ram:Subject')),
                (string) self::validarEtiqueta($informacionAdicional->xpath('ram:Content'))
            );
            $informacionList[] = $informacionAdicionalPFIDTO;
        }

        $envioPFIImp = $xml->xpath('//rsm:SPSConsignment//ram:ConsigneeSPSParty')[0];
        $envioRecepcionPFIList = [];
        $envioRecepcionCFEDTOImp = new EnvioRecepcionPFIDTO(
            (string) self::validarEtiqueta($envioPFIImp->xpath('ram:Name')),
            (string) self::validarEtiqueta($envioPFIImp->xpath('ram:SpecifiedSPSAddress//ram:LineOne')),
            (string) self::validarEtiqueta($envioPFIImp->xpath('ram:SpecifiedSPSAddress//ram:LineTwo')),
            (string) self::validarEtiqueta($envioPFIImp->xpath('ram:SpecifiedSPSAddress//ram:LineThree')),
            (string) self::validarEtiqueta($envioPFIImp->xpath('ram:SpecifiedSPSAddress//ram:LineFour')),
            (string) self::validarEtiqueta($envioPFIImp->xpath('ram:SpecifiedSPSAddress//ram:LineFive')),
            'Importer'
        );

        $envioRecepcionPFIList[] = $envioRecepcionCFEDTOImp;

        $envioPfiExp = $xml->xpath('//rsm:SPSConsignment//ram:ConsignorSPSParty')[0];

        $envioRecepcionPFIDTOExp = new EnvioRecepcionPFIDTO(
            (string) self::validarEtiqueta($envioPfiExp->xpath('ram:Name')),
            (string) self::validarEtiqueta($envioPfiExp->xpath('ram:SpecifiedSPSAddress//ram:LineOne')),
            (string) self::validarEtiqueta($envioPfiExp->xpath('ram:SpecifiedSPSAddress//ram:LineTwo')),
            (string) self::validarEtiqueta($envioPfiExp->xpath('ram:SpecifiedSPSAddress//ram:LineThree')),
            (string) self::validarEtiqueta($envioPfiExp->xpath('ram:SpecifiedSPSAddress//ram:LineFour')),
            (string) self::validarEtiqueta($envioPfiExp->xpath('ram:SpecifiedSPSAddress//ram:LineFive')),
            'Exporter'
        );

        $envioRecepcionPFIList[] = $envioRecepcionPFIDTOExp;

        $paisesInterrelacionadosList = [];

        $paisesInterrelacionadosPFIPE = new PaisesInterrelacionadosPFIDTO(
            self::validarEtiqueta($xml->xpath('//rsm:SPSConsignment//ram:UnloadingBaseportSPSLocation//ram:ID')),
            self::validarEtiqueta($xml->xpath('//rsm:SPSConsignment//ram:UnloadingBaseportSPSLocation//ram:Name')),
            'PUNTO_ENTRADA'
        );

        $paisesInterrelacionadosList[] = $paisesInterrelacionadosPFIPE;

        $paisesInterrelacionadosPFIPS = new PaisesInterrelacionadosPFIDTO(
            self::validarEtiqueta($xml->xpath('//rsm:SPSConsignment//ram:LoadingBaseportSPSLocation//ram:ID')),
            self::validarEtiqueta($xml->xpath('//rsm:SPSConsignment//ram:LoadingBaseportSPSLocation//ram:Name')),
            'PUNTO_SALIDA'
        );

        $paisesInterrelacionadosList[] = $paisesInterrelacionadosPFIPS;

        $productosPermisos = $xml->xpath('//rsm:SPSConsignment//ram:IncludedSPSConsignmentItem//ram:IncludedSPSTradeLineItem');
        $productosPermidoList = [];

        foreach ($productosPermisos as $productoPermiso) {
            $additionalInformationSPSNote = $productoPermiso->xpath('ram:AdditionalInformationSPSNote');

            $productosInformacionAdicionalList = [];
            foreach ($additionalInformationSPSNote as $additionformationNote) {

                $subject = $additionformationNote->xpath('ram:Subject');
                if (empty($subject)) {
                    $subject = '';
                } else {
                    $subject = (string)$subject[0];
                }

                $poductosInformacionAdicionalPFIDTO = new ProductosInformacionAdicionalPFIDTO(
                    $subject,
                    (string) self::validarEtiqueta($additionformationNote->xpath('ram:Content'))
                );

                $productosInformacionAdicionalList[] = $poductosInformacionAdicionalPFIDTO;
            }

            $applicableSPSClassification = $productoPermiso->xpath('ram:ApplicableSPSClassification');

            $applicableSPSClassificationList = [];
            foreach ($applicableSPSClassification as $applicableClassification) {

                $classCode = $applicableClassification->xpath('ram:ClassCode');
                if (empty($classCode)) {
                    $classCode = $applicableClassification->xpath('ram:ClassName');
                }

                $productosClasesPFIDTO = new ProductosClasesPFIDTO(
                    (string) self::validarEtiqueta($applicableClassification->xpath('ram:SystemName')),
                    (string) $classCode[0]
                );
                $applicableSPSClassificationList[] = $productosClasesPFIDTO;
            }

            $physicalSPSPackage = $productoPermiso->xpath('ram:PhysicalSPSPackage');

            $physicalSPSPackageList = [];
            foreach ($physicalSPSPackage as $physicalPackage) {
                $productoDescripcionPaquetePFIDTO = new ProductoDescripcionPaquetePFIDTO(
                    (string) self::validarEtiqueta($physicalPackage->xpath('ram:LevelCode')),
                    (string) self::validarEtiqueta($physicalPackage->xpath('ram:TypeCode')),
                    (int) self::validarEtiqueta($physicalPackage->xpath('ram:ItemQuantity'))
                );
                $physicalSPSPackageList[] = $productoDescripcionPaquetePFIDTO;
            }

            $productosPermisoPFIDTO = new ProductosPersmisoPFIDTO(
                (string) self::validarEtiqueta($productoPermiso->xpath('ram:Description')),
                (string) self::validarEtiqueta($productoPermiso->xpath('ram:CommonName')),
                (string) self::validarEtiqueta($productoPermiso->xpath('ram:ScientificName')),
                (string) self::validarEtiqueta($productoPermiso->xpath('ram:IntendedUse')),
                (float) self::validarEtiqueta($productoPermiso->xpath('ram:NetWeightMeasure')),
                (float) self::validarEtiqueta($productoPermiso->xpath('ram:GrossWeightMeasure')),
                (float) self::validarEtiqueta($productoPermiso->xpath('ram:NetVolumeMeasure')),
                (float) self::validarEtiqueta($productoPermiso->xpath('ram:GrossVolumeMeasure')),
                (string) self::validarEtiqueta($productoPermiso->xpath('ram:OriginSPSCountry//ram:ID')),
                (string) self::validarEtiqueta($productoPermiso->xpath('ram:OriginSPSCountry//ram:Name')),
                (string) self::validarEtiqueta($productoPermiso->xpath('ram:OriginSPSCountry//ram:SubordinateSPSCountrySubDivision//ram:Name')),
                $productosInformacionAdicionalList,
                $applicableSPSClassificationList,
                $physicalSPSPackageList,
            );

            $productosPermidoList[] = $productosPermisoPFIDTO;
        }

        $permisoPFIDTO = new PermisoPFIDTO(
            numeroPermiso: (string) self::validarEtiqueta($xml->xpath('//rsm:SPSExchangedDocument//ram:ID')),
            estadoCambio: (int) self::validarEtiqueta($xml->xpath('//rsm:SPSExchangedDocument//ram:TypeCode')),
            fechaEmision: self::validarEtiquetaFecha($xml->xpath('//rsm:SPSExchangedDocument//ram:IssueDateTime//udt:DateTimeString')),
            diasVigencia: (int) self::validarEtiqueta($xml->xpath('//rsm:SPSExchangedDocument//ram:EffectiveDateTime//udt:DateTimeString')),
            proteccionFitosanitaria: (string) self::validarEtiqueta($xml->xpath('//rsm:SPSExchangedDocument//ram:IssuerSPSParty//ram:Name')),
            nombreFuncionario: (string) self::validarEtiqueta($xml->xpath('//rsm:SPSExchangedDocument//ram:IssuerSPSParty//ram:SpecifiedSPSPerson//ram:Name')),
            documentoFechaEmision: $datosDocumento['fechaEmision'],
            referennciaOriginalEmision: (string) $datosDocumento['referenciaOriginal'],
            numeroFitosanitarioOriginal: $datosDocumento['numeroCertificado'],
            archivoAdjuntoPath: $datosDocumento['pathDocumento'],
            descripcionDocumento: (int) $datosDocumento['descripcionDocumento'],
            lugarEmision: (string) $xml->xpath('//rsm:SPSExchangedDocument//ram:SignatorySPSAuthentication//ram:IssueSPSLocation//ram:Name')[0] ?? null,
            medioTransporte: (string) self::validarEtiqueta($xml->xpath('//rsm:SPSConsignment//ram:MainCarriageSPSTransportMovement//ram:ID')),
            modoTransporte: (int)self::validarEtiqueta($xml->xpath('//rsm:SPSConsignment//ram:MainCarriageSPSTransportMovement//ram:ModeCode')),
            nombreTrasporte: (string) self::validarEtiqueta($xml->xpath('//rsm:SPSConsignment//ram:MainCarriageSPSTransportMovement//ram:UsedSPSTransportMeans//ram:Name')),
            numeroSello: (string) self::validarEtiqueta($xml->xpath('//rsm:SPSConsignment//ram:UtilizedSPSTransportEquipment//ram:AffixedSPSSeal//ram:ID')),
            puntoOrigen: 0,
            envioRecepcionDocumento: '',
            informacionAdicionalPFI: $informacionList,
            envioRecepcionPFI: $envioRecepcionPFIList,
            paisesInterrelacionadosPFI: $paisesInterrelacionadosList,
            productosCertificadosPFI: $productosPermidoList
        );
        return $permisoPFIDTO;
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

            Storage::makeDirectory(IntercomConstants::PATH_STORE_PFI_PDF);
            Storage::disk('agrocalidad')->put(IntercomConstants::PATH_STORE_PFI_PDF . '/PFI_' . $numeroFitosanitarioOriginal. '.pdf', $contenidoPdf);

            return [
                'fechaEmision' => $dateCertificadoFechaEmision->format('Y-m-d H:i:d'),
                'referenciaOriginal' => self::validarEtiqueta($etiqueta[0]->xpath('ram:RelationshipTypeCode')),
                'numeroCertificado' => $numeroFitosanitarioOriginal,
                'pathDocumento' =>IntercomConstants::PATH_STORE_PFI_PDF . '/PFI_' . $numeroFitosanitarioOriginal. '.pdf',
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
