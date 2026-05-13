<?php

namespace Modules\Intercom\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;
use Modules\Intercom\DTOs\InteroperabilidadCANDTO;
use Modules\Intercom\Domain\Xml\Classes\CertificateAdapter;
use Modules\Intercom\Domain\Xml\Classes\XMLGenerator;
use Modules\Intercom\Domain\Xml\Classes\XmlValidator;
use Modules\Intercom\Domain\Xml\IntercomConstants;
use Modules\Intercom\Domain\Xml\Strategies\PythosanitaryExportCertificate;
use Modules\Intercom\DTOs\EntradaBusquedaDTO;
use Modules\Intercom\DTOs\ErroresDocumentosRecibidosDTO;
use Modules\Intercom\DTOs\ErroresNotificacionesResultadoEnvioDTO;
use Modules\Intercom\DTOs\FormatoPendientesDTO;
use Modules\Intercom\DTOs\ListadoFormatoPendientesDTO;
use Modules\Intercom\DTOs\NotificacionesResultadosEnvioDAO;

class IntercomCfeService
{
    public $interomApiService;

    public function __construct()
    {
        $this->interomApiService = new IntercomApiServices();
    }

    public function sendExportPhytosanitaryCertificateMba002(\stdClass $certificate)
    {
        try {
            $transformed_certificate = CertificateAdapter::transform($certificate);

            $generator = new XMLGenerator();
            $generator->setXmlStrategy(new PythosanitaryExportCertificate($transformed_certificate));

            $xml = $generator->generateXML();

            Storage::makeDirectory(IntercomConstants::PATH_STORE_CFE_XML);
            Storage::disk('agrocalidad')->put(IntercomConstants::PATH_STORE_CFE_XML . '/CFE_' . $transformed_certificate->ID . '.xml', $xml);

            $base64xml = base64_encode($xml);

            $xmlData = '<root>
            <fechaEmision>' . $transformed_certificate->IssueDateTime_DateTimeString . '</fechaEmision>
            <codigoFormato>' . $transformed_certificate->ID . '</codigoFormato>
            <puntoDestino>' . $certificate->codigo_pais_detino . '</puntoDestino>
            <formato>' . $base64xml . '</formato>
            </root>';

            $result = $this->interomApiService->sendMbaCFE0002($xmlData);

            $idCertificadoFitosanitario = $certificate->id_certificado_fitosanitario;

            $dataXml = '';
            $statusConexion = $result['status'];
            $estadoDocumento = '';
            if ((int)$statusConexion == 200) {
                $dataXml = $result['data'];
                $estadoDocumento = 'INGRESO_INTERCOM';
            } else {
                $dataXml = $result['data'];
                $estadoDocumento = 'ERROR';
            }

            $interoperabilidadCanService = new InteroperabilidadCANService();
            $interopeabilidadConulta = $interoperabilidadCanService->buscarFitosanitarioPorId($idCertificadoFitosanitario, 'CFE', 'M-BA002');
            if (!$interopeabilidadConulta->isEmpty()) {
                $interperabilidadModelo = $interopeabilidadConulta[0];
                $interperabilidadModelo->parth_archivo = IntercomConstants::PATH_STORE_CFE_XML . '/CFE_' . $transformed_certificate->ID . '.xml';
                $interoperabilidadCanService->actualizarInteroperabilidad($interperabilidadModelo, $estadoDocumento);
            } else {

                $interopCANDTO = new InteroperabilidadCANDTO(
                    idCertificadoPermiso: (int) $idCertificadoFitosanitario,
                    codigoFitosanitarioO: $certificate->codigo_certificado,
                    codigoFitosanitarioC: $transformed_certificate->ID,
                    tipoDocumento: 'CFE',
                    metodo: 'M-BA002',
                    estadoDocumento: $estadoDocumento,
                    respuestaIntercom: $dataXml,
                    parthArchivo: IntercomConstants::PATH_STORE_CFE_XML . '/CFE_' . $transformed_certificate->ID . '.xml',
                    ejecutadoPor: 'TAREA PROGRAMADA'
                );
                $interoperabilidadCanService->guardarInteroperabilidad($interopCANDTO);
            }
            return $dataXml;
        } catch (\Exception $e) {
            Log::info("Error class IntercomCfeService: " . $e->getMessage());
        }
    }

    public function descargarXMLInteroperabilidad($idCertificadoPermiso, $codigoCertificadoC, $tipoMetodo)
    {
        try {
            $interoperabilidadCanService = new InteroperabilidadCANService();
            $rutaArchivo = $interoperabilidadCanService->buscarXMLFitosanitario($idCertificadoPermiso, $codigoCertificadoC, $tipoMetodo);
            if (!empty($rutaArchivo) && isset($rutaArchivo[0])) {
                if (Storage::disk('agrocalidad')->exists($rutaArchivo[0]->parth_archivo)) {
                    $contenido = Storage::disk('agrocalidad')->get($rutaArchivo[0]->parth_archivo);
                    $base64 = base64_encode($contenido);

                    return [
                        'status' => '200',
                        'archivo'  => $base64,
                    ];
                } else {
                    return [
                        'status' => '401',
                        'error'  => 'No se encotro el XML Solicitado ',
                    ];
                }
            } else {
                return [
                    'status' => '401',
                    'error'  => 'No se encotro el XML Solicitado ',
                ];
            }
        } catch (\Exception $e) {
            Log::info("Error class IntercomCfeService: " . $e->getMessage());
            return [
                'status' => '401',
                'error'  => 'No se encotro el XML Solicitado Error: ' . $e->getMessage(),
            ];
        }
    }

    public function getListExportPhytosanitaryCertificateMba005($parametrosBusqueda)
    {
        try {
            $resultadoConsulta = $this->interomApiService->getListadoCFERecibidosPDMba005($parametrosBusqueda);
            if ($resultadoConsulta['status'] == 200) {

                if (empty(trim($resultadoConsulta['data']))) {
                    return str_replace('%error%', 'Errores XML: no esxiste elemento que procesar', IntercomConstants::RESPUESTA_INTERCOM_NEGATIVA);
                }

                $xmlObject =  simplexml_load_string($resultadoConsulta['data']);

                $listaFormatoPendiente = [];
                foreach ($xmlObject->SPSCertificates->SPSCertificate as $certificados) {
                    $dateFechaEnvio = Date::parse($certificados->IssueDateTime);
                    $dateFechaRecepcion = Date::parse($certificados->ReceivingDateTime);

                    $listadoFormatoPendientesDTO = new ListadoFormatoPendientesDTO(
                        (int) $certificados->FunctionalReferenceID,
                        (string) $certificados->FormatID,
                        (string) $certificados->ID,
                        (string) $certificados->Submitter->IdentificationIssuingCountryCode,
                        $dateFechaEnvio->format('Y-m-d H:i:d'),
                        (string) $certificados->RequestStatus,
                        $dateFechaRecepcion->format('Y-m-d H:i:d')
                    );
                    $listaFormatoPendiente[] = $listadoFormatoPendientesDTO;
                }

                $formatoPendientesDTO = new FormatoPendientesDTO(
                    funcion: (int) $xmlObject->Function,
                    tipoCodigo: (string) $xmlObject->TypeCode,
                    tipoFormato: 'CFE',
                    numeroPagina: (int) $xmlObject->Pagination->CurrentPage,
                    totalPagina: (int) $xmlObject->Pagination->TotalPages,
                    totalRegistro: (int) $xmlObject->Pagination->TotalRecords,
                    tamanoPagina: (int) $xmlObject->Pagination->PageSize,
                    listaFormatoPendiente: $listaFormatoPendiente
                );

                $formatosPendientesCANService = new FormatosPendientesCANService();
                $result = $formatosPendientesCANService->guardarListaFormatosPendientes($formatoPendientesDTO);
                return $resultadoConsulta['data'];
            } else if ($resultadoConsulta['status'] == 400) {
                return [
                    'status' => 400,
                    'data'   => $resultadoConsulta['data'],
                ];
            }
        } catch (\Exception $e) {
            Log::info("Error class IntercomCfeService: " . $e->getMessage());
            return [
                'status' => '401',
                'error'  => 'No se pudo ejcutar el metodo M-BA005 Error:' . $e->getMessage(),
            ];
        }
    }

    public function solicitarEnvioReenvioMbamba011($parametrosSolicitud)
    {
        try {
            $certificadoCFECANService = new CertificadoCFECANService();

            if ($parametrosSolicitud->puntoOrigen == '') {

                return [
                    'status' => 401,
                    'data'   => 'Error, punto de origen es obligatorio',
                ];
            }

            $listaCertificadoCFE = $certificadoCFECANService->obtenerCFEPorNumero($parametrosSolicitud);

            if (!$listaCertificadoCFE->isEmpty()) {

                return [
                    'status' => 401,
                    'data'   => 'El Certificado Fitosanitario de Exportación esta Registrado',
                ];
            }

            $idFormato = $parametrosSolicitud->idFormato == '' ? '' : '<idFormato>' . $parametrosSolicitud->idFormato . '</idFormato>';
            $codigoFormato = $parametrosSolicitud->codigoFormato == '' ? '' : '<codigoFormato>' . $parametrosSolicitud->codigoFormato . '</codigoFormato>';

            $xmlData = '<root>' .
                $idFormato .
                $codigoFormato .
                '<puntoOrigen>' . $parametrosSolicitud->puntoOrigen . '</puntoOrigen>
            </root>';

            $datosEstrada = $this->interomApiService->solicitudEnvioFormatoCfeMBA011($xmlData);
            if ($datosEstrada['status'] == 200) {

                if (empty(trim($datosEstrada['data']))) {

                    return str_replace('%error%', 'Errores XML: no esxiste elemento que procesar', IntercomConstants::RESPUESTA_INTERCOM_NEGATIVA_MBA011);
                }

                $xmlObject = simplexml_load_string($datosEstrada['data']);

                // Convertir el XML a un array (opcional)
                $arrayData = json_decode(json_encode($xmlObject), true);

                $intormacionImportada = $arrayData['SPSCertificate'];
                $base64String = $intormacionImportada['Base64File'];

                $status = $arrayData['Status'];

                // Decodificar la cadena Base64
                $decodedString = base64_decode($base64String);

                $validator = XmlValidator::validateData($decodedString);
                $validator = XmlValidator::validate($decodedString, IntercomConstants::CFE_MBA002_XSD_ESTANDAR);
                $validator = XmlValidator::validate($decodedString, IntercomConstants::CFE_MBA002_XSD_VALIDACION);

                $datoCFEmba011 = CertificateAdapter::transformGetCFEmba002($decodedString);
                $datoCFEmba011->puntoOrigen = $parametrosSolicitud->puntoOrigen;
                $datoCFEmba011->envioRecepcionDocumento = 'Pendiente';

                $result = $certificadoCFECANService->guardarCertificadoCFECan($datoCFEmba011);

                Storage::makeDirectory(IntercomConstants::PATH_STORE_CFE_XML);
                Storage::disk('agrocalidad')->put(IntercomConstants::PATH_STORE_CFE_XML . '/CFE_' . $datoCFEmba011->numeroCertificado . '.xml', $decodedString);

                $interopCANDTO = new InteroperabilidadCANDTO(
                    idCertificadoPermiso: $result->id_cfe,
                    codigoFitosanitarioO: $datoCFEmba011->numeroCertificado,
                    codigoFitosanitarioC: $datoCFEmba011->numeroCertificado,
                    tipoDocumento: 'CFE',
                    metodo: 'M-BA011',
                    estadoDocumento: 'INGRESO_INTERCOM_CFE',
                    respuestaIntercom: IntercomConstants::RESPUESTA_INTERCOM_POSITIVA_MBA011,
                    parthArchivo: IntercomConstants::PATH_STORE_CFE_XML . '/CFE_' . $datoCFEmba011->numeroCertificado . '.xml',
                    ejecutadoPor: 'SERVICIO API'
                );

                $interoperabilidadCanService = new InteroperabilidadCANService();
                $interoperabilidadCanService->guardarInteroperabilidad($interopCANDTO);
                $this->solicitarEnvioReenvioMbamba023($result);
                return IntercomConstants::RESPUESTA_INTERCOM_POSITIVA_MBA011;
            } else if ($datosEstrada['status'] == 400) {
                $xmlObject = simplexml_load_string($datosEstrada['data']);
                $fechaActual = Carbon::now();
                $erroresDocumentosRecibidosDTO = new ErroresDocumentosRecibidosDTO(
                    codigoFormato: $parametrosSolicitud->codigoFormato,
                    fechaEmision: $fechaActual,
                    puntoOrigen: $parametrosSolicitud->puntoOrigen,
                    errorPresentado: $xmlObject,
                    tipoDocumento: 'CFI',
                    estadoEnvio: 'Pendiente'
                );

                return $datosEstrada['data'];
            }
        } catch (\Exception $e) {

            $erroresDocumentosRecibidosDTO = new ErroresDocumentosRecibidosDTO(
                codigoFormato: $parametrosSolicitud->codigoFormato,
                fechaEmision: Carbon::now()->format('d/m/Y H:i'),
                puntoOrigen: $parametrosSolicitud->puntoOrigen,
                errorPresentado: $e->getMessage(),
                tipoDocumento: 'CFI',
                estadoEnvio: 'Pendiente'
            );

            $rroresDocumentosRecibidosService = new ErroresDocumentosRecibidosService();
            $rroresDocumentosRecibidosService->guardarErroresDocumentosService($erroresDocumentosRecibidosDTO);

            $errorDocumentoCFE = new \stdClass();
            $errorDocumentoCFE->numero_certificado = $parametrosSolicitud->codigoFormato;
            $errorDocumentoCFE->codigo_formato = str_replace('%error%', 'Error: :' . $e->getMessage(), IntercomConstants::RESPUESTA_INTERCOM_NEGATIVA_MBA011);

            return str_replace('%error%', 'Error: :' . $e->getMessage(), IntercomConstants::RESPUESTA_INTERCOM_NEGATIVA_MBA011);
        }
    }

    public function solicitarEnvioReenvioMbamba023($certificadoCFE)
    {
        try {
            $envioResultado = '<root> 
                                <codigoFormato>'.$certificadoCFE->numero_certificado.'</codigoFormato> 
                                <recibidoConExito>true</recibidoConExito>
                               </root>';
            $result =  $this->interomApiService->solicitudEnvioFormatoCfeMBA023($envioResultado);

            if ($result['status'] == 200) {
                $certificadoCFE->envio_recepcion_documento = 'Enviado';
                $certificadoCFECANService = new CertificadoCFECANService();
                $certificadoCFECANService->actualizadoCertificadoCFE($certificadoCFE);

                 $interopCANDTO = new InteroperabilidadCANDTO(
                    idCertificadoPermiso: $certificadoCFE->id_cfe,
                    codigoFitosanitarioO: $certificadoCFE->numero_certificado,
                    codigoFitosanitarioC: $certificadoCFE->numero_certificado,
                    tipoDocumento: 'CFE',
                    metodo: 'M-BA023',
                    estadoDocumento: 'INGRESO_INTERCOM',
                    respuestaIntercom: $result['data'],
                    parthArchivo: '',
                    ejecutadoPor: 'TAREA PROGRAMADA'
                );

                $interoperabilidadCanService = new InteroperabilidadCANService();
                $interoperabilidadCanService->guardarInteroperabilidad($interopCANDTO);

            } else if ($result['status'] == 400) {
                $interopCANDTO = new InteroperabilidadCANDTO(
                    idCertificadoPermiso: $certificadoCFE->id_cfe,
                    codigoFitosanitarioO: $certificadoCFE->numero_certificado,
                    codigoFitosanitarioC: $certificadoCFE->numero_certificado,
                    tipoDocumento: 'CFE',
                    metodo: 'M-BA023',
                    estadoDocumento: 'ERROR',
                    respuestaIntercom: $result['data'],
                    parthArchivo: '',
                    ejecutadoPor: 'TAREA PROGRAMADA'
                );

                $interoperabilidadCanService = new InteroperabilidadCANService();
                $interoperabilidadCanService->guardarInteroperabilidad($interopCANDTO);

                $certificadoCFE->envio_recepcion_documento = 'Error Envio';
                $certificadoCFECANService = new CertificadoCFECANService();
                $certificadoCFECANService->actualizadoCertificadoCFE($certificadoCFE);
            }

            return $result['data'];
        } catch (\Exception $e) {
            Log::info("Error class IntercomCfeService: " . $e->getMessage());
        }
    }

    public function enviarErrorDocumentoMbamba023($errorDocumentoCFE)
    {
        try {
            $envioResultado = '<Response xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
                                <Function>27</Function>
                                <TypeCode>M-BA023</TypeCode>
                                <Errors>
                                    <Error>
                                        <ErrorDetails>
                                            <ErrorDetail>
                                                <Type>Fallo al notificar al punto de origen</Type>
                                                <ValidationCode>25</ValidationCode>
                                                <Description>' . $errorDocumentoCFE->codigo_formato . '</Description>
                                            </ErrorDetail>
                                        </ErrorDetails>
                                    </Error>
                                </Errors>
                                <SPSCertificate>
                                    <ID>' . $errorDocumentoCFE->numero_certificado . '</ID>
                                    <RequestStatus>63</RequestStatus>
                                </SPSCertificate>
                           </Response>';

            $result =  $this->interomApiService->solicitudEnvioFormatoCfeMBA023($envioResultado);

            if ($result['status'] == 200) {
                $errorDocumentoCFE->estado_envio = 'Enviado';
            } else {
                Log::info("Error al enviar la respueta MBA023 del CFE: " . $result['error']);
            }
        } catch (\Exception $e) {
            Log::info("Error class IntercomCfeService: " . $e->getMessage());
        }
    }

    public function recibirCFEMba002MEX501($datosEstrada)
    {
        $detener = false;
        $respuetaIntercom = '';
        $codigoFormato = 'S/N';
        $puntoOrigenE = 'S/N';

        try {
            if (empty(trim($datosEstrada))) {
                $detener = true;
                $respuetaIntercom = str_replace('%error%', 'Errores XML: no esxiste elemento que procesar', IntercomConstants::RESPUESTA_INTERCOM_NEGATIVA);
            }
            // Convertir XML a un objeto SimpleXMLElement
            if (!$detener) {
                $xmlObject = simplexml_load_string($datosEstrada);

                $validadorXml = new XmlValidatorService();
                $erroresXml = $validadorXml->validarEstructuraMEX501($xmlObject);

                if (!empty($erroresXml)) {
                    $erroresPresentados = '';
                    $cont = 1;
                    foreach ($erroresXml as $error) {
                        $erroresPresentados .= $cont . ' ' . $error . ' - ';
                        $cont++;
                    }
                    $detener = true;
                    $respuetaIntercom = str_replace('%error%', $erroresPresentados, IntercomConstants::RESPUESTA_INTERCOM_NEGATIVA);
                }
            }

            if (!$detener) {
                // Convertir el XML a un array (opcional)
                $arrayData = json_decode(json_encode($xmlObject), true);
                $codigoFormato = $arrayData['codigoFormato'];
                $puntoOrigenE = $arrayData['puntoOrigen'];
                // Obtener la cadena Base64
                $base64String = $arrayData['formato'];

                // Decodificar la cadena Base64
                $decodedString = base64_decode($base64String);

                // Verificar si la decodificación fue exitosa
                if (!$decodedString) {
                    $detener = true;
                    $respuetaIntercom = str_replace('%error%', 'Error: No se pudo decodificar la cadena Base64', IntercomConstants::RESPUESTA_INTERCOM_NEGATIVA);
                }
            }

            if (!$detener) {
                $puntoOrigen = array_column(IntercomConstants::CAN_COUNTRIES, 'code');

                if (!in_array($arrayData['puntoOrigen'], $puntoOrigen)) {
                    $detener = true;
                    $respuetaIntercom = str_replace('%error%', 'Error: El punto de origen: ' . $arrayData['puntoOrigen'] . ' no coincide con el catálogo de siglas de países según estándar ISO-3166', IntercomConstants::RESPUESTA_INTERCOM_NEGATIVA);
                }
            }

            if (!$detener) {
                $validator = XmlValidator::validateData($decodedString);
                $validator = XmlValidator::validate($decodedString, IntercomConstants::CFE_MBA002_XSD_ESTANDAR);
                $validator = XmlValidator::validate($decodedString, IntercomConstants::CFE_MBA002_XSD_VALIDACION);

                $certificadoCFECANService = new CertificadoCFECANService();
                $parametrosSolicitud = new \stdClass();

                $parametrosSolicitud->codigoFormato = $arrayData['codigoFormato'];
                $parametrosSolicitud->puntoOrigen = $arrayData['puntoOrigen'];

                $listaCertificadoCFE = $certificadoCFECANService->obtenerCFEPorNumero($parametrosSolicitud);

                if (!$listaCertificadoCFE->isEmpty()) {
                    $detener = true;
                    $respuetaIntercom = str_replace('%error%', 'Error: El Certificado Fitosanitario de Exportación Nro. ' . $arrayData['codigoFormato'] . ' se encuentra registrado', IntercomConstants::RESPUESTA_INTERCOM_NEGATIVA);
                }
            }

            if (!$detener) {
                $datoCFEmab501 = CertificateAdapter::transformGetCFEmba002($decodedString);
                $datoCFEmab501->puntoOrigen = $arrayData['puntoOrigen'];
                $datoCFEmab501->envioRecepcionDocumento = 'Pendiente';

                if ($datoCFEmab501->numeroCertificado != $arrayData['codigoFormato']) {
                    $detener = true;
                    $respuetaIntercom = str_replace('%error%', 'Error: El ID del documento no coincide con el código formato ' . $datoCFEmab501->numeroCertificado . ' ' . $arrayData['codigoFormato'], IntercomConstants::RESPUESTA_INTERCOM_NEGATIVA);
                }
            }

            if (!$detener) {
                $result = $certificadoCFECANService->guardarCertificadoCFECan($datoCFEmab501);

                Storage::makeDirectory(IntercomConstants::PATH_STORE_CFE_XML);
                Storage::disk('agrocalidad')->put(IntercomConstants::PATH_STORE_CFE_XML . '/CFE_' . $datoCFEmab501->numeroCertificado . '.xml', $decodedString);

                $interopCANDTO = new InteroperabilidadCANDTO(
                    idCertificadoPermiso: (int) $result->id_cfe,
                    codigoFitosanitarioO: $datoCFEmab501->numeroCertificado,
                    codigoFitosanitarioC: $datoCFEmab501->numeroCertificado,
                    tipoDocumento: 'CFE',
                    metodo: 'M-EX501',
                    estadoDocumento: 'INGRESO_INTERCOM_CFE',
                    respuestaIntercom: IntercomConstants::RESPUESTA_INTERCOM_POSITIVA,
                    parthArchivo: IntercomConstants::PATH_STORE_CFE_XML . '/CFE_' . $datoCFEmab501->numeroCertificado . '.xml',
                    ejecutadoPor: 'SERVICIO API'
                );

                $interoperabilidadCanService = new InteroperabilidadCANService();
                $interoperabilidadCanService->guardarInteroperabilidad($interopCANDTO);
                $respuetaIntercom =  IntercomConstants::RESPUESTA_INTERCOM_POSITIVA;
            }
        } catch (\Exception $e) {
            $fechaActual = now();
            $erroresDocumentosRecibidosDTO = new ErroresDocumentosRecibidosDTO(
                codigoFormato: $codigoFormato,
                fechaEmision: $fechaActual->toDateTimeString(),
                puntoOrigen: $puntoOrigenE,
                errorPresentado: $e->getMessage(),
                tipoDocumento: 'CFI',
                estadoEnvio: 'Pendiente'
            );

            $rroresDocumentosRecibidosService = new ErroresDocumentosRecibidosService();
            $rroresDocumentosRecibidosService->guardarErroresDocumentosService($erroresDocumentosRecibidosDTO);

            $respuetaIntercom = str_replace('%error%', 'Error:' . $e->getMessage() . ' Exite simbolos que no petencen en las etiqueta fechaEmision, codigoFormato, puntoOrigen o formato', IntercomConstants::RESPUESTA_INTERCOM_NEGATIVA);
        }
        return $respuetaIntercom;
    }

    public function descargarPdfCertificado($iCfe)
    {
        try {
            $certificadoCFECANService = new CertificadoCFECANService();
            $rutaArchivo = $certificadoCFECANService->obtenerArchivoPdfPorID($iCfe);
            if (!empty($rutaArchivo) && isset($rutaArchivo[0])) {
                if (Storage::disk('agrocalidad')->exists($rutaArchivo[0]->archivo_adjunto_path)) {
                    $contenido = Storage::disk('agrocalidad')->get($rutaArchivo[0]->archivo_adjunto_path);
                    $base64 = base64_encode($contenido);

                    return [
                        'status' => '200',
                        'archivo'  => $base64,
                    ];
                } else {
                    return [
                        'status' => '401',
                        'error'  => 'No se encotro el PDF Solicitado ',
                    ];
                }
            } else {
                return [
                    'status' => '401',
                    'error'  => 'No se encotro el PDF Solicitado ',
                ];
            }
        } catch (\Exception $e) {
            Log::info("Error class IntercomCfeService: " . $e->getMessage());
            return [
                'status' => '401',
                'error'  => 'No se pudo ejcutar la solicitud Error: ' . $e->getMessage(),
            ];
        }
    }

    public function statusObtenerCFEMEX503($datosBusqueda)
    {

        if (empty(trim($datosBusqueda))) {
            return str_replace('%error%', 'Errores XML: no esxiste elemento que procesar', IntercomConstants::RESPUESTA_INTERCOM_NEGATIVA_MEX503);
        }
        try {
            $contenidoXmlSeguro = preg_replace('/&(?!amp;|lt;|gt;|quot;|apos;)/', '&amp;', $datosBusqueda);
            $xmlObject = simplexml_load_string($contenidoXmlSeguro);

            $validadorXml = new XmlValidatorService();
            $erroresXml = $validadorXml->validarEstructuraMEX503($xmlObject);

            if (!empty($erroresXml)) {
                $erroresPresentados = '';
                $cont = 1;
                foreach ($erroresXml as $error) {
                    $erroresPresentados .= $cont . ' ' . $error . ' - ';
                    $cont++;
                }
                return str_replace('%error%', 'Errores XML: ' . $erroresPresentados, IntercomConstants::RESPUESTA_INTERCOM_NEGATIVA_MEX503);
            }

            $arrayData = json_decode(json_encode($xmlObject), true);

            $puntoOrigen = array_column(IntercomConstants::CAN_COUNTRIES, 'code');

            if (!in_array($arrayData['puntoDestino'], $puntoOrigen)) {
                return str_replace('%error%', 'Error: El punto de destino: "' . $arrayData['puntoDestino'] . '" no coincide con el catálogo de siglas de países según estándar ISO-3166', IntercomConstants::RESPUESTA_INTERCOM_NEGATIVA_MEX503);
            }

            $entradaBusqueda = new EntradaBusquedaDTO(
                codigoFormato: $arrayData['codigoFormato'],
                puntoDestino: $arrayData['puntoDestino']
            );

            $certificadoCFECANService = new CertificadoCFECANService();
            $result = $certificadoCFECANService->statusObtenerCFE($entradaBusqueda);

            return $result;
        } catch (\Exception $e) {
            return str_replace('%error%', 'Error: XML mal construido', IntercomConstants::RESPUESTA_INTERCOM_NEGATIVA_MEX503);
        }
    }

    public function statusObtenerCFEMEX502($datoEntrada)
    {

        if (empty(trim($datoEntrada))) {
            return str_replace('%error%', 'Errores XML: no esxiste elemento que procesar', IntercomConstants::RESPUESTA_INTERCOM_NEGATIVA);
        }

        try {
            $contenidoXmlSeguro = preg_replace('/&(?!amp;|lt;|gt;|quot;|apos;)/', '&amp;', $datoEntrada);
            $xmlObject = simplexml_load_string($contenidoXmlSeguro);

            $validadorXml = new XmlValidatorService();
            $erroresXml = $validadorXml->validarEstructuraMEX502($xmlObject);

            if (!empty($erroresXml)) {
                $erroresPresentados = '';
                $cont = 1;
                foreach ($erroresXml as $error) {
                    $erroresPresentados .= $cont . ' ' . $error . ' - ';
                    $cont++;
                }
                return str_replace('%error%', 'Errores XML: ' . $erroresPresentados, IntercomConstants::RESPUESTA_INTERCOM_NEGATIVA_MEX502);
            }

            foreach ($xmlObject->formatos->detalleFormato as $detallFormato) {
                $puntoOrigen = array_column(IntercomConstants::CAN_COUNTRIES, 'code');

                if (!in_array($detallFormato->puntoDestino, $puntoOrigen)) {
                    return str_replace('%error%', 'Error: El punto de origen: "' . $xmlObject->formatos->detalleFormato->puntoDestino . '" no coincide con el catálogo de siglas de países según estándar ISO-3166', IntercomConstants::RESPUESTA_INTERCOM_NEGATIVA_MEX502);
                }

                $codFormato = array_column(IntercomConstants::CAN_ESTADO_DOCUMENTO, 'codigo_intercom');

                if (!in_array($detallFormato->estadoDocumento, $codFormato)) {
                    return str_replace('%error%', 'Error: Código del estado del documento no encotrado', IntercomConstants::RESPUESTA_INTERCOM_NEGATIVA_MEX502);
                }
            }

            foreach ($xmlObject->formatos->detalleFormato as $detallFormato) {
                $listaErroresDocumentos = [];
                if ($detallFormato->erroresDocumento->errorDocumento != null) {
                    foreach ($detallFormato->erroresDocumento->errorDocumento as $errorDocumento) {
                        $errDocumento = new ErroresNotificacionesResultadoEnvioDTO(
                            (int) $errorDocumento->idError,
                            (string) $errorDocumento->detalleError
                        );
                        $listaErroresDocumentos[] = $errDocumento;
                    }
                }

                $notificacionesResultadosEnvioDAO = new NotificacionesResultadosEnvioDAO(
                    idSolicitud: (string) $xmlObject->idSolicitud,
                    codigoFormato: (string) $detallFormato->codigoFormato,
                    puntoDestino: (string) $detallFormato->puntoDestino,
                    fechaRecepcionIntercom: (string) $detallFormato->fechaRecepcionIntercom,
                    fechaRecepcionDestino: (string) $detallFormato->fechaRecepcionDestino,
                    estadoDocumento: (string) $detallFormato->estadoDocumento,
                    superoCantidadIntento: ($detallFormato->superaCantidadIntentos == 'true' ? 1 : 0),
                    tipoDocumento: 'CFE',
                    erroresNotificacionesResultadoEnvio: $listaErroresDocumentos
                );

                $notificacionesResultadosEnvioService = new NotificacionesResultadosEnvioService();
                $notificacionesResultadosEnvio = $notificacionesResultadosEnvioService->guardarNotificacionesResultadoEnvio($notificacionesResultadosEnvioDAO);
            }
            return $notificacionesResultadosEnvio;
        } catch (\Exception $e) {
            return str_replace('%error%', 'Error: XML mal construido', IntercomConstants::RESPUESTA_INTERCOM_NEGATIVA_MEX502);
        }
    }
}
