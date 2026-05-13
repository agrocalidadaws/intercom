<?php

namespace Modules\Intercom\Domain\Xml;

class IntercomConstants
{
    public const CFE_MBA002_XSD_ESTANDAR = "schemas/XSD-Estandar-CFE/cfe-xsd/data/standard/SPSCertificate_17p0.xsd";
    public const CFE_MBA002_XSD_VALIDACION = "schemas/XSD-Validaciones-CFE/cfe-xsd/data/standard/SPSCertificate_17p0.xsd";

    public const PFI_MBA002_XSD_ESTANDAR = "schemas/XSD-Estandar-PFI/pfi-xsd/data/standard/SPSImportPermit_1p0.xsd";
    public const PFI_MBA002_XSD_VALIDACION = "schemas/XSD-Validaciones-PFI/pfi-xsd/data/standard/SPSImportPermit_1p0.xsd";
    
    public const CAN_IPPC = [['code' => 657], ['code' => 851], ['code' => 312]];

    public const FECHA_INICIO = '2024-02-01';
    public const NUMERO_CONSULTA = 2;

    public const CAN_COUNTRIES = [
        [
            'location_id' => 49,
            'code' => 'CO',
            'name' => 'Colombia'
        ],
        [
            'location_id' => 1928,
            'code' => 'PE',
            'name' => 'Perú'
        ],
        [
            'location_id' => 29,
            'code' => 'BO',
            'name' => 'Bolivia'
        ],
        [
            'location_id' => 66,
            'code' => 'EC',
            'name' => 'Ecuador'
        ]
    ];

    public const CAN_ESTADO_DOCUMENTO = [
        [
            'codigo_intercom' => '01',
            'estado' => 'Recibido por INTERCOM',
            'descripcion' => 'Refiere a cuando INTERCOM recibe el documento. '
        ],
        [
            'codigo_intercom' => '02',
            'estado' => 'Validado',
            'descripcion' => 'Refiere a cuando el documento supera las validaciones (ej: esquema, firma).'
        ],
        [
            'codigo_intercom' => '03',
            'estado' => 'Reprogramado',
            'descripcion' => 'Refiere a cuando el envío al punto destino no es exitoso, por lo cual se reprograma.'
        ],
        [
            'codigo_intercom' => '04',
            'estado' => 'Entregado',
            'descripcion' => 'Refiere a cuando el documento ha sido entregado exitosamente al destinatario.'
        ],
        [
            'codigo_intercom' => '05',
            'estado' => 'Rechazado',
            'descripcion' => 'Refiere a cuando el destino rechaza el documento.'
        ],
        [
            'codigo_intercom' => '06',
            'estado' => 'Archivado',
            'descripcion' => 'Refiere a cuando el documento ha superado el límite de intentos sin poder ser entregado.'
        ],
        [
            'codigo_intercom' => '07',
            'estado' => 'Anulado',
            'descripcion' => 'Refiere a cuando el documento es anulado.'
        ]
    ];

    public const PATH_STORE_CFE_XML='certificadoFitoSanitarioExportacion/XML';
    public const PATH_STORE_CFE_PDF='certificadoFitoSanitarioExportacion/PDF';

    public const PATH_STORE_PFI_XML='permisoFitoSanitarioImportacion/XML';
    public const PATH_STORE_PFI_PDF='permisoFitoSanitarioImportacion/PDF';

    public const MBA_CFE002_CFE = 'FITO/CFE/formato/envio';
    public const MBA_CFE002_PFI = 'FITO/PFI/formato/envio';

    public const MBA_CFE005_CFE = 'FITO/CFE/formato/consultaestado';
    public const MBA_CFE005_PFI = 'FITO/PFI/formato/consultaestado';

    public const MBA_CFE011_CFE = 'FITO/CFE/formato/solicitudenvio';
    public const MBA_CFE011_PFI = 'FITO/PFI/formato/solicitudenvio';

    public const MBA_CFE023_CFE = 'FITO/CFE/formato/envioresultado';
    public const MBA_CFE023_PFI = 'FITO/PFI/formato/envioresultado';

    public const RESPUESTA_INTERCOM_POSITIVA = '<Response><Function>11</Function><TypeCode>M-EX501</TypeCode><Status><NameCode>73</NameCode><StatementDescription/></Status></Response>';
    public const RESPUESTA_INTERCOM_NEGATIVA = '<Response><Function>11</Function><TypeCode>M-EX501</TypeCode><Status><NameCode>63</NameCode><StatementDescription>%error%</StatementDescription></Status></Response>';

    public const RESPUESTA_INTERCOM_POSITIVA_MBA011 = '<Response><Function>11</Function><TypeCode>M-BA011</TypeCode><Status><NameCode>73</NameCode><StatementDescription/></Status></Response>';
    public const RESPUESTA_INTERCOM_NEGATIVA_MBA011 = '<Response><Function>11</Function><TypeCode>M-BA011</TypeCode><Status><NameCode>63</NameCode><StatementDescription>%error%</StatementDescription></Status></Response>';

    public const RESPUESTA_INTERCOM_POSITIVA_MEX502 = '<Response><Function>11</Function><TypeCode>M-EX502</TypeCode><Status><NameCode>73</NameCode><StatementDescription/></Status></Response>';
    public const RESPUESTA_INTERCOM_NEGATIVA_MEX502 = '<Response><Function>11</Function><TypeCode>M-EX502</TypeCode><Status><NameCode>63</NameCode><StatementDescription>%error%</StatementDescription></Status></Response>';

    public const RESPUESTA_INTERCOM_POSITIVA_MEX503 = '<Response><Function>11</Function><TypeCode>M-EX503</TypeCode><Status><NameCode>73</NameCode><StatementDescription/></Status></Response>';
    public const RESPUESTA_INTERCOM_NEGATIVA_MEX503 = '<Response><Function>11</Function><TypeCode>M-EX503</TypeCode><Status><NameCode>63</NameCode><StatementDescription>%error%</StatementDescription></Status></Response>';

}
