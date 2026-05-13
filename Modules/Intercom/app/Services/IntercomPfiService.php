<?php

namespace Modules\Intercom\Services;

use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;
use Modules\Intercom\Domain\Xml\Classes\CertificateAdapterPFI;
use Modules\Intercom\Domain\Xml\Classes\XMLGenerator;
use Modules\Intercom\Domain\Xml\Classes\XmlValidator;
use Modules\Intercom\Domain\Xml\IntercomConstants;
use Modules\Intercom\Domain\Xml\Strategies\PythosanitaryImportPermit;
use Modules\Intercom\DTOs\EntradaBusquedaDTO;
use Modules\Intercom\DTOs\ErroresDocumentosRecibidosDTO;
use Modules\Intercom\DTOs\ErroresNotificacionesResultadoEnvioDTO;
use Modules\Intercom\DTOs\FormatoPendientesDTO;
use Modules\Intercom\DTOs\InteroperabilidadCANDTO;
use Modules\Intercom\DTOs\ListadoFormatoPendientesDTO;
use Modules\Intercom\DTOs\NotificacionesResultadosEnvioDAO;

class IntercomPfiService
{

    public function __construct()
    {
        $this->interomApiService = new IntercomApiServices();
    }

    public function sendPhytonsanitaryImportPermitMba002($permiso)
    {
        try {
            $transformed_permit = CertificateAdapterPFI::transform($permiso);
            $generator = new XMLGenerator();
            $generator->setXmlStrategy(new PythosanitaryImportPermit($transformed_permit));

            $xml = $generator->generateXML();

            Storage::makeDirectory(IntercomConstants::PATH_STORE_PFI_XML);
            Storage::disk('agrocalidad')->put(IntercomConstants::PATH_STORE_PFI_XML . '/PFI_' . $transformed_permit->ID . '.xml', $xml);

            $base64xml = base64_encode($xml);

            $xmlData = '<root>
            <fechaEmision>' . $transformed_permit->fechaCambio . '</fechaEmision>
            <codigoFormato>' . $transformed_permit->ID . '</codigoFormato>
            <puntoDestino>' . $permiso->codigo_exportacion . '</puntoDestino>
            <formato>' . $base64xml . '</formato>
            <version>1.0</version>
            <causal>Motivo Causal</causal>
            </root>';

            $result = $this->interomApiService->sendMbaPFI0002($xmlData);

            $idCertificadoFitosanitario = $permiso->id_importacion;

            $dataXml = '';
            $statusConexion = $result['status'];
            if ((int)$statusConexion == 200) {
                $dataXml = $result['data'];
                $estadoDocumento = 'INGRESO_INRERCOM';
            } else {
                $dataXml = $result['data'];
                $estadoDocumento = 'ERROR';
            }

            $interoperabilidadCanService = new InteroperabilidadCANService();
            $interopeabilidadConulta = $interoperabilidadCanService->buscarFitosanitarioPorId($idCertificadoFitosanitario, 'PFI', 'M-BA002');
            if (!$interopeabilidadConulta->isEmpty()) {
                $interopeabilidadConultaModelo = $interopeabilidadConulta[0];
                $interopeabilidadConultaModelo->parth_archivo = IntercomConstants::PATH_STORE_PFI_XML . '/PFI_' . $transformed_permit->ID . '.xml';
                $interoperabilidadCanService->actualizarInteroperabilidad($interopeabilidadConulta[0], $estadoDocumento);
            } else {
                $interopCANDTO = new InteroperabilidadCANDTO(
                    idCertificadoPermiso: (int) $idCertificadoFitosanitario,
                    codigoFitosanitarioO: $permiso->id_vue,
                    codigoFitosanitarioC: $transformed_permit->ID,
                    tipoDocumento: 'PFI',
                    metodo: 'M-BA002',
                    estadoDocumento: $estadoDocumento,
                    respuestaIntercom: $dataXml,
                    parthArchivo: IntercomConstants::PATH_STORE_PFI_XML . '/PFI_' . $transformed_permit->ID . '.xml',
                    ejecutadoPor: 'TAREA PROGRAMADA'
                );
                $interoperabilidadCanService->guardarInteroperabilidad($interopCANDTO);
            }

            return $dataXml;
        } catch (\Exception $e) {
            Log::info("Error class class IntercomPfiService: " . $e->getMessage());
        }
    }

    public function getListPhytonsanitaryImportPermitMba005($parametrosBusqueda)
    {
        try {
            $resultadoConsulta = $this->interomApiService->getListadoPFIRecibidosPDMba005($parametrosBusqueda);

            if ($resultadoConsulta['status'] == 200) {

                if (empty(trim($resultadoConsulta['data']))) {
                    return str_replace('%error%', 'Errores XML: no esxiste elemento que procesar', IntercomConstants::RESPUESTA_INTERCOM_NEGATIVA);
                }

                $xmlObject =  simplexml_load_string($resultadoConsulta['data']);

                $listaFormatoPendiente = [];
                foreach ($xmlObject->SPSImportPermits->SPSImportPermit as $permisoImportacion) {
                    $dateFechaEnvio = Date::parse($permisoImportacion->IssueDateTime);
                    $dateFechaRecepcion = Date::parse($permisoImportacion->ReceivingDateTime);

                    $listadoFormatoPendientesDTO = new ListadoFormatoPendientesDTO(
                        (int) $permisoImportacion->FunctionalReferenceID,
                        (string) $permisoImportacion->FormatID,
                        (string) $permisoImportacion->ID,
                        (string) $permisoImportacion->Submitter->IdentificationIssuingCountryCode,
                        $dateFechaEnvio->format('Y-m-d H:i:d'),
                        (string) $permisoImportacion->RequestStatus,
                        $dateFechaRecepcion->format('Y-m-d H:i:d')
                    );
                    $listaFormatoPendiente[] = $listadoFormatoPendientesDTO;
                }

                $formatoPendientesDTO = new FormatoPendientesDTO(
                    funcion: (int) $xmlObject->Function,
                    tipoCodigo: (string) $xmlObject->TypeCode,
                    tipoFormato: 'PFI',
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
            return $resultadoConsulta['data'];
        } catch (\Exception $e) {
            Log::info("Error class class IntercomPfiService: " . $e->getMessage());
            return [
                'status' => '401',
                'error'  => 'No se pudo ejectuar el metodo M-BA005 Error:' . $e->getMessage(),
            ];
        }
    }

    public function solicitarEnvioReenvioMbamba011($parametrosSolicitud)
    {
        try {
            $permisoPFICANService = new PermisoPFICANService();

            if ($parametrosSolicitud->puntoOrigen == '') {
                return [
                    'status' => 401,
                    'data'   => 'Error e punto origen es obligatorio',
                ];
            }

            $listaPermisoPFI = $permisoPFICANService->obtenerPFIPorNumero($parametrosSolicitud);

            if (!$listaPermisoPFI->isEmpty()) {
                return [
                    'status' => 401,
                    'data'   => 'El Permiso Fitosanitario de Importación ya se encuentra registrado',
                ];
            }

            $idFormato = $parametrosSolicitud->idFormato == '' ? '' : '<idFormato>' . $parametrosSolicitud->idFormato . '</idFormato>';
            $codigoFormato = $parametrosSolicitud->codigoFormato == '' ? '' : '<codigoFormato>' . $parametrosSolicitud->codigoFormato . '</codigoFormato>';

            $xmlData = '<root>' .
                $idFormato .
                $codigoFormato .
                '<puntoOrigen>' . $parametrosSolicitud->puntoOrigen . '</puntoOrigen>
                </root>';

            $datosEstrada = $this->interomApiService->solicitudEnvioFormatoPfiMBA011($xmlData);

            if ($datosEstrada['status'] == 200) {

                if (empty(trim($datosEstrada['data']))) {
                    return str_replace('%error%', 'Errores XML: no esxiste elemento que procesar', IntercomConstants::RESPUESTA_INTERCOM_NEGATIVA_MBA011);
                }

                $xmlObject = simplexml_load_string($datosEstrada['data']);

                if (!empty($erroresXml)) {
                    $erroresPresentados = '';
                    $cont = 1;
                    foreach ($erroresXml as $error) {
                        $erroresPresentados .= $cont . ' ' . $error . ' - ';
                        $cont++;
                    }

                    return str_replace('%error%', 'Errores XML: ' . $erroresPresentados, IntercomConstants::RESPUESTA_INTERCOM_NEGATIVA_MBA011);
                }

                // Convertir el XML a un array (opcional)
                $arrayData = json_decode(json_encode($xmlObject), true);

                // Obtener la cadena Base64
                $intormacionImportada = $arrayData['SPSImportPermit'];
                $base64String = $intormacionImportada['Base64File'];

                // Decodificar la cadena Base64
                $decodedString = base64_decode($base64String);

                $validator = XmlValidator::validate($decodedString, IntercomConstants::PFI_MBA002_XSD_ESTANDAR);
                $validator = XmlValidator::validate($decodedString, IntercomConstants::PFI_MBA002_XSD_VALIDACION);

                $datoPFImab501 = CertificateAdapterPFI::transformGetPFImba002($decodedString);

                $datoPFImab501->puntoOrigen = $parametrosSolicitud->puntoOrigen;
                $datoPFImab501->envioRecepcionDocumento = 'Pendiente';

                $result = $permisoPFICANService->guardarPermisoPFICan($datoPFImab501);

                Storage::makeDirectory(IntercomConstants::PATH_STORE_PFI_XML);
                Storage::disk('agrocalidad')->put(IntercomConstants::PATH_STORE_PFI_XML . '/PFI_' . $datoPFImab501->numeroPermiso . '.xml', $decodedString);

                $interopCANDTO = new InteroperabilidadCANDTO(
                    idCertificadoPermiso: (int) $result->id_pfi,
                    codigoFitosanitarioO: $datoPFImab501->numeroPermiso,
                    codigoFitosanitarioC: $datoPFImab501->numeroPermiso,
                    tipoDocumento: 'PFI',
                    metodo: 'M-BA011',
                    estadoDocumento: 'INGRESO_INTERCOM_PFI',
                    respuestaIntercom: IntercomConstants::RESPUESTA_INTERCOM_POSITIVA_MBA011,
                    parthArchivo: IntercomConstants::PATH_STORE_PFI_XML . '/PFI_' . $datoPFImab501->numeroPermiso . '.xml',
                    ejecutadoPor: 'SERVICIO API'
                );

                $interoperabilidadCanService = new InteroperabilidadCANService();
                $interoperabilidadCanService->guardarInteroperabilidad($interopCANDTO);
                $this->solicitarEnvioReenvioMbamba023($result);
                return IntercomConstants::RESPUESTA_INTERCOM_POSITIVA_MBA011;
            } else if ($datosEstrada['status'] == 400) {
                $errorDocumentoPFI = new \stdClass();
                $errorDocumentoPFI->codigo_formato = $parametrosSolicitud->codigoFormato;
                $errorDocumentoPFI->error_presentado = $datosEstrada['data'];
                return $datosEstrada['data'];
            }
        } catch (\Exception $e) {

            $erroresDocumentosRecibidosDTO = new ErroresDocumentosRecibidosDTO(
                codigoFormato: $parametrosSolicitud->codigoFormato,
                fechaEmision: Carbon::now()->format('d/m/Y H:i'),
                puntoOrigen: $parametrosSolicitud->puntoOrigen,
                errorPresentado: $e->getMessage(),
                tipoDocumento: 'PFI',
                estadoEnvio: 'Pendiente'
            );

            $rroresDocumentosRecibidosService = new ErroresDocumentosRecibidosService();
            $rroresDocumentosRecibidosService->guardarErroresDocumentosService($erroresDocumentosRecibidosDTO);

            $errorDocumentoPFI = new \stdClass();
            $errorDocumentoPFI->codigo_formato = $parametrosSolicitud->codigoFormato;
            $errorDocumentoPFI->error_presentado = str_replace('%error%', 'Error: :' . $e->getMessage(), IntercomConstants::RESPUESTA_INTERCOM_NEGATIVA_MBA011);
            return str_replace('%error%', 'Error: :' . $e->getMessage(), IntercomConstants::RESPUESTA_INTERCOM_NEGATIVA_MBA011);
        }
    }

    public function solicitarEnvioReenvioMbamba023($permisoPFI)
    {
        try {

            $envioResultado = '<root> 
                                <codigoFormato>'.$permisoPFI->numero_permiso.'</codigoFormato> 
                                <recibidoConExito>true</recibidoConExito>
                               </root>';

            $result =  $this->interomApiService->solicitudEnvioFormatoPfiMBA023($envioResultado);

            if ($result['status'] == 200) {
                $permisoPFI->envio_recepcion_documento = 'Enviado';
                $permisoPFICANService = new PermisoPFICANService();
                $resultBD = $permisoPFICANService->actualizarPermiso($permisoPFI);
                $interopCANDTO = new InteroperabilidadCANDTO(
                    idCertificadoPermiso: $permisoPFI->id_pfi,
                    codigoFitosanitarioO: $permisoPFI->numero_permiso,
                    codigoFitosanitarioC: $permisoPFI->numero_permiso,
                    tipoDocumento: 'PFI',
                    metodo: 'M-BA023',
                    estadoDocumento: 'INGRESO_INTERCOM',
                    respuestaIntercom: $result['data'],
                    parthArchivo: '',
                    ejecutadoPor: 'SERVICIO API'
                );
                $interoperabilidadCanService = new InteroperabilidadCANService();
                $interoperabilidadCanService->guardarInteroperabilidad($interopCANDTO);
            } else {
                $interopCANDTO = new InteroperabilidadCANDTO(
                    idCertificadoPermiso: $permisoPFI->id_pfi,
                    codigoFitosanitarioO: $permisoPFI->numero_permiso,
                    codigoFitosanitarioC: $permisoPFI->numero_permiso,
                    tipoDocumento: 'PFI',
                    metodo: 'M-BA023',
                    estadoDocumento: 'ERROR',
                    respuestaIntercom: $result['data'],
                    parthArchivo: '',
                    ejecutadoPor: 'SERVICIO API'
                );

                $interoperabilidadCanService = new InteroperabilidadCANService();
                $interoperabilidadCanService->guardarInteroperabilidad($interopCANDTO);

                $permisoPFI->envio_recepcion_documento = 'Error Envio';
                $permisoPFICANService = new PermisoPFICANService();
                $resultBD = $permisoPFICANService->actualizarPermiso($permisoPFI);
            }
            return $result;
        } catch (\Exception $e) {
            Log::info("Error class IntercomPfiService: " . $e->getMessage());
        }
    }

    public function enviarErrorDocumentoMba023($errorDocumentoCFE)
    {
        try {
            $envioResultado = '<root> 
                                <codigoFormato>'.$errorDocumentoCFE->codigo_formato.'</codigoFormato> 
                                <recibidoConExito>false</recibidoConExito>
                               </root>';
            $result =  $this->interomApiService->solicitudEnvioFormatoPfiMBA023($envioResultado);

            if ($result['status'] == 200) {
                $errorDocumentoCFE->estado_envio = 'Enviado';
                $erroresDocumentosRecibidosService = new ErroresDocumentosRecibidosService();
                $erroresDocumentosRecibidosService->actualizarErroresDocumentosRecibidos($errorDocumentoCFE);
            } else {
                Log::info("Error al enviar la respueta MBA023 del PFI: " . $result['error']);
            }
        } catch (\Exception $e) {
            Log::info("Error class IntercomPfiService: " . $e->getMessage());
        }
    }

    public function recibirPFIMba002MEX501($datosEstrada)
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
                    $respuetaIntercom = str_replace('%error%', 'Errores XML: ' . $erroresPresentados, IntercomConstants::RESPUESTA_INTERCOM_NEGATIVA);
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
                    $respuetaIntercom = str_replace('%error%', 'Error: El punto de origen: "' . $arrayData['puntoOrigen'] . '" no coincide con el catálogo de siglas de países según estándar ISO-3166', IntercomConstants::RESPUESTA_INTERCOM_NEGATIVA);
                }
            }

            if (!$detener) {
                $parametrosSolicitud = new \stdClass();
                $parametrosSolicitud->codigoFormato = $arrayData['codigoFormato'];
                $parametrosSolicitud->puntoOrigen = $arrayData['puntoOrigen'];

                $permisoPFICANService = new PermisoPFICANService();
                $listaPermisoPFI = $permisoPFICANService->obtenerPFIPorNumero($parametrosSolicitud);

                if (!$listaPermisoPFI->isEmpty()) {
                    $detener = true;
                    $respuetaIntercom = str_replace('%error%', 'Error: El Permiso Fitosanitario de Importación Nro.' . $arrayData['codigoFormato'] . ' ya se encuentra registrado', IntercomConstants::RESPUESTA_INTERCOM_NEGATIVA);
                }
            }

            if (!$detener) {
                $validator = XmlValidator::validate($decodedString, IntercomConstants::PFI_MBA002_XSD_ESTANDAR);
                $validator = XmlValidator::validate($decodedString, IntercomConstants::PFI_MBA002_XSD_VALIDACION);

                $datoPFImab501 = CertificateAdapterPFI::transformGetPFImba002($decodedString);
                $datoPFImab501->puntoOrigen = $arrayData['puntoOrigen'];
                $datoPFImab501->envioRecepcionDocumento = 'Pendiente';

                if ($datoPFImab501->numeroPermiso != $arrayData['codigoFormato']) {
                    $detener = true;
                    $respuetaIntercom = str_replace('%error%', 'Error: El ID del documento no coincide ' . $datoPFImab501->numeroPermiso . ' con el código formato', IntercomConstants::RESPUESTA_INTERCOM_NEGATIVA);
                }
            }

            if (!$detener) {
                $result = $permisoPFICANService->guardarPermisoPFICan($datoPFImab501);

                Storage::makeDirectory(IntercomConstants::PATH_STORE_PFI_XML);
                Storage::disk('agrocalidad')->put(IntercomConstants::PATH_STORE_PFI_XML . '/PFI_' . $datoPFImab501->numeroPermiso . '.xml', $decodedString);

                $interopCANDTO = new InteroperabilidadCANDTO(
                    idCertificadoPermiso: (int) $result->id_pfi,
                    codigoFitosanitarioO: $datoPFImab501->numeroPermiso,
                    codigoFitosanitarioC: $datoPFImab501->numeroPermiso,
                    tipoDocumento: 'PFI',
                    metodo: 'M-EX501',
                    estadoDocumento: 'INGRESO_INTERCOM_PFI',
                    respuestaIntercom: IntercomConstants::RESPUESTA_INTERCOM_POSITIVA,
                    parthArchivo: IntercomConstants::PATH_STORE_PFI_XML . '/PFI_' . $datoPFImab501->numeroPermiso . '.xml',
                    ejecutadoPor: 'SERVICIO API'
                );

                $interoperabilidadCanService = new InteroperabilidadCANService();
                $interoperabilidadCanService->guardarInteroperabilidad($interopCANDTO);

                $respuetaIntercom = IntercomConstants::RESPUESTA_INTERCOM_POSITIVA;
            }
        } catch (\Exception $e) {
            $fechaActual = now();
            $erroresDocumentosRecibidosDTO = new ErroresDocumentosRecibidosDTO(
                codigoFormato: $codigoFormato,
                fechaEmision: $fechaActual->toDateTimeString(),
                puntoOrigen: $puntoOrigenE,
                errorPresentado: $e->getMessage(),
                tipoDocumento: 'PFI',
                estadoEnvio: 'Pendiente'
            );

            $rroresDocumentosRecibidosService = new ErroresDocumentosRecibidosService();
            $rroresDocumentosRecibidosService->guardarErroresDocumentosService($erroresDocumentosRecibidosDTO);

            $respuetaIntercom = str_replace('%error%', 'Error:' . $e->getMessage() . ' Exite simbolos que no petencen en las etiqueta fechaEmision, codigoFormato, puntoOrigen o formato', IntercomConstants::RESPUESTA_INTERCOM_NEGATIVA);
        }
        return $respuetaIntercom;
    }

    public function descargarPdfPermiso($idPfi)
    {
        try {
            $permisoPFICANService = new PermisoPFICANService();
            $rutaArchivo = $permisoPFICANService->obtenerArvhivoPfd($idPfi);
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
                    'error'  => 'No se encotro el PDF Solicitado ' . $rutaArchivo,
                ];
            }
        } catch (\Exception $e) {
            Log::info("Error class IntercomPfiService: " . $e->getMessage());
            return [
                'status' => '401',
                'error'  => 'No se pudo ejcutar la solicitud Error: ' . $e->getMessage(),
            ];
        }
    }

    public function statusObtenerPFIMEX503($datosBusqueda)
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
                return str_replace('%error%', 'El punto de destino: "' . $arrayData['puntoDestino'] . '"', IntercomConstants::RESPUESTA_INTERCOM_NEGATIVA_MEX503);
            }

            $entradaBusqueda = new EntradaBusquedaDTO(
                codigoFormato: $arrayData['codigoFormato'],
                puntoDestino: $arrayData['puntoDestino']
            );

            $permisoPFICANService = new PermisoPFICANService();
            $result = $permisoPFICANService->statusObtenerPFI($entradaBusqueda);

            return $result;
        } catch (\Exception $e) {
            return str_replace('%error%', 'Error: XML mal construido', IntercomConstants::RESPUESTA_INTERCOM_NEGATIVA_MEX503);
        }
    }

    public function statusObtenerPFIMEX502($datoEntrada)
    {
        if (empty(trim($datoEntrada))) {
            return str_replace('%error%', 'Errores XML: no esxiste elemento que procesar', IntercomConstants::RESPUESTA_INTERCOM_NEGATIVA_MEX502);
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
                    return str_replace('%error%', 'El punto de origen: ' . $xmlObject->formatos->detalleFormato->puntoDestino . 'no coincide con el catálogo de siglas de países según estándar ISO-3166', IntercomConstants::RESPUESTA_INTERCOM_NEGATIVA_MEX502);
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
                    tipoDocumento: 'PFI',
                    erroresNotificacionesResultadoEnvio: $listaErroresDocumentos
                );

                $notificacionesResultadosEnvioService = new NotificacionesResultadosEnvioService();
                $notificacionesResultadosEnvio = $notificacionesResultadosEnvioService->guardarNotificacionesResultadoEnvio($notificacionesResultadosEnvioDAO);
            }
            return $notificacionesResultadosEnvio;
        } catch (\Exception $e) {
            return str_replace('%error%', 'Error: XML mal construido ' . $e->getMessage(), IntercomConstants::RESPUESTA_INTERCOM_NEGATIVA_MEX502);
        }
    }
}
