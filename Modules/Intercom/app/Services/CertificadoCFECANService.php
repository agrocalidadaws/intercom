<?php

namespace Modules\Intercom\Services;

use Illuminate\Support\Facades\Log;
use Modules\Intercom\Models\cfe\CertificadoCFE;
use Illuminate\Support\Facades\DB;
use Modules\Intercom\Domain\Xml\IntercomConstants;

class CertificadoCFECANService
{

    public function guardarCertificadoCFECan($certificadoCFEDTO)
    {
        DB::beginTransaction();
        try {
            $certificado = CertificadoCFE::create(
                [
                    'numero_certificado' => $certificadoCFEDTO->numeroCertificado,
                    'nombre_certificado' => $certificadoCFEDTO->nombreCertificado,
                    'estado_cambio' => $certificadoCFEDTO->estadoCambio,
                    'fecha_emision' => $certificadoCFEDTO->fechaEmision,
                    'proteccion_fitosanitaria' => $certificadoCFEDTO->proteccionFitosanitaria,
                    'documento_fecha_emision' => $certificadoCFEDTO->documentoFechaEmision,
                    'referencia_original_emision' => $certificadoCFEDTO->referennciaOriginalEmision,
                    'numero_fitosanitario_original' => $certificadoCFEDTO->numeroFitosanitarioOriginal,
                    'archivo_adjunto_path' => $certificadoCFEDTO->archivoAdjuntoPath,
                    'descripcion_documento' => $certificadoCFEDTO->descripcionDocumento,
                    'lugar_emision' => $certificadoCFEDTO->lugarEmision,
                    'funcionario_autorizado' => $certificadoCFEDTO->funcionarioAutorizado,
                    'certificacion_estandar' => $certificadoCFEDTO->cartificacionEstandar,
                    'medio_transporte' => $certificadoCFEDTO->medioTransporte,
                    'modo_transporte' => $certificadoCFEDTO->modoTransporte,
                    'nombre_trasporte' => $certificadoCFEDTO->nombreTrasporte,
                    'numero_sello' => $certificadoCFEDTO->numeroSello,
                    'punto_origen' => $certificadoCFEDTO->puntoOrigen,
                    'envio_recepcion_documento' => $certificadoCFEDTO->envioRecepcionDocumento
                ]
            );

            $informacionAdicionalCFEDTO = $certificadoCFEDTO->informacionAdicionalCFE;
            $informacionAdicionalCFEList = [];

            foreach ($informacionAdicionalCFEDTO as $informacionAdicional) {
                $informacionAdicionalCFE = [
                    'descripcion' => $informacionAdicional->subject,
                    'contenido' => $informacionAdicional->contenido
                ];
                $informacionAdicionalCFEList[] = $informacionAdicionalCFE;
            }

            $certificado->informacionAdicionalCFE()->createMany($informacionAdicionalCFEList);

            $envioRecepcionCFEDTO = $certificadoCFEDTO->envioRecepcionCFE;
            $envioRecepcionCFEList = [];

            foreach ($envioRecepcionCFEDTO as $envioRecepcion) {
                $envioRecepcionCFE = [
                    'nombre_consigna' => $envioRecepcion->nombreCosigna,
                    'direccion_consignador_uno' => $envioRecepcion->direccionLineOne,
                    'direccion_consignador_dos' => $envioRecepcion->direccionLineTwo,
                    'direccion_consignador_tres' => $envioRecepcion->direccionLineThree,
                    'direccion_consignador_cuatro' => $envioRecepcion->direccionLineFour,
                    'direccion_consignador_cinco' => $envioRecepcion->direccionLineFive,
                    'tipo' => $envioRecepcion->tipo
                ];
                $envioRecepcionCFEList[] = $envioRecepcionCFE;
            }

            $certificado->envioRecepcionCFE()->createMany($envioRecepcionCFEList);

            $paisesInterrelacionadosCFEDTO = $certificadoCFEDTO->paisesInterrelacionadosCFE;
            $paisesInterrelacionadosCFEList = [];

            foreach ($paisesInterrelacionadosCFEDTO as $paisesInterrelacionados) {
                $paisesInterrelacionadosCFE = [
                    'id_pais' => $paisesInterrelacionados->idPais,
                    'nombre' => $paisesInterrelacionados->nombre,
                    'tipo' => $paisesInterrelacionados->tipo,
                ];
                $paisesInterrelacionadosCFEList[] =  $paisesInterrelacionadosCFE;
            }

            $certificado->paisesInterrelacionadosCFE()->createMany($paisesInterrelacionadosCFEList);

            $productosCertificadosCFEDTO = $certificadoCFEDTO->productosCertificadosCFEDTO;

            foreach ($productosCertificadosCFEDTO as $productosCertificados) {
                $productosCertificadosCFE = $certificado->productosCertificadosCFE()->createMany([[
                    'descripcion' => $productosCertificados->descripcion,
                    'nombre_comun' => $productosCertificados->nombreComun,
                    'nombre_cientifico' => $productosCertificados->nombreCientifico,
                    'producto_ippc' => $productosCertificados->productoIPPC,
                    'peso_neto' => $productosCertificados->pesoNeto,
                    'peso_bruto' => $productosCertificados->pesoBruto,
                    'volumen_neto' => $productosCertificados->volumenNeto,
                    'volumen_bruto' => $productosCertificados->volumenBruto,
                    'pais_orige_id' => $productosCertificados->paisOrigenId,
                    'nombre_zona_dentro_po' => $productosCertificados->nombreZonaDentroPO
                ]]);

                $productosInformacionAdicionalCFEDTO = $productosCertificados->productosInformacionAdicionalCFE;
                $productosInformacionAdicionalCFEList = [];

                foreach ($productosInformacionAdicionalCFEDTO as $productosInformacionAdicional) {
                    $productosInformacionAdicionalCFE = [
                        'pinfa_descripcion' => $productosInformacionAdicional->pinfaSubject,
                        'pinfa_contenido' => $productosInformacionAdicional->pinfaContenido
                    ];

                    $productosInformacionAdicionalCFEList[] = $productosInformacionAdicionalCFE;
                }

                $productosCertificadosCFE[0]->productosInformacionAdicionalCFE()->createMany($productosInformacionAdicionalCFEList);

                $productosClasesCFEDTO = $productosCertificados->productosClasesCFE;
                $productosClasesCFEList = [];

                foreach ($productosClasesCFEDTO as $productosClases) {
                    $productosClasesCFE = [
                        'nombre_sistema' => $productosClases->systemName,
                        'clase_codigo' => $productosClases->classeCodigo
                    ];
                    $productosClasesCFEList[] = $productosClasesCFE;
                }

                $productosCertificadosCFE[0]->productosClasesCFE()->createMany($productosClasesCFEList);

                $productoDescripcionPaqueteCFEDTO = $productosCertificados->productoDescripcionPaqueteCFE;
                $productoDescripcionPaqueteCFEList = [];

                foreach ($productoDescripcionPaqueteCFEDTO as $productoDescripcionPaquete) {
                    $productoDescripcionPaqueteCFE = [
                        'codigo_nivel_embalaje' => $productoDescripcionPaquete->codigoNivelEmbalaje,
                        'codigo_tipo_paquete' => $productoDescripcionPaquete->cofigoTipoPaquete,
                        'numero_paquetes' => $productoDescripcionPaquete->numeroPaquetes
                    ];
                    $productoDescripcionPaqueteCFEList[] = $productoDescripcionPaqueteCFE;
                }

                $productosCertificadosCFE[0]->productoDescripcionPaqueteCFE()->createMany($productoDescripcionPaqueteCFEList);

                $productosTratamientoCFEDTO = $productosCertificados->productosTratamientosCFE;

                foreach ($productosTratamientoCFEDTO as $productosTratamiento) {
                    $productosTratamientoCFE = $productosCertificadosCFE[0]->productosTratamientoCFE()->createMany([[
                        'tipo_codigo_tra' => $productosTratamiento->tipoCodigo,
                        'fecha_inicio_tra' => $productosTratamiento->fechaIncio,
                        'fecha_final_tra' => $productosTratamiento->fechaFinal,
                        'duracion_tra' => $productosTratamiento->duracion,
                    ]]);

                    $productosTiposTratamientosCFEDTO = $productosTratamiento->productosTiposTratamientosCFE;
                    $productosTiposTratamientosCFEList = [];

                    foreach ($productosTiposTratamientosCFEDTO as $productosTiposTratamientos) {
                        $productosTiposTratamientosCFE = [
                            'descripcion_tra_uno' => $productosTiposTratamientos->descripcionTraOne,
                            'descripcion_tra_dos' => $productosTiposTratamientos->descripcionTraTwo
                        ];
                        $productosTiposTratamientosCFEList[] = $productosTiposTratamientosCFE;
                    }
                    $productosTratamientoCFE[0]->productosTiposTratamientosCFE()->createMany($productosTiposTratamientosCFEList);
                }
            }
            DB::commit();
            return $certificado;
        } catch (\Exception $e) {
            Log::info('Error clase CertificadoCFECANService método guardarCertificadoCFECan: ' . $e->getMessage());
            DB::rollBack();
            return str_replace('%error%', 'Error: ' . $e->getMessage(), IntercomConstants::RESPUESTA_INTERCOM_NEGATIVA);
            IntercomConstants::RESPUESTA_INTERCOM_NEGATIVA;
        }
    }

    public function statusObtenerCFE($entradaBusqueda)
    {
        try {
            $certificado = CertificadoCFE::where('numero_certificado', '=', $entradaBusqueda->codigoFormato)
                ->where('punto_origen', '=', $entradaBusqueda->puntoDestino)
                ->get();

            if (!$certificado->isEmpty()) {
                return IntercomConstants::RESPUESTA_INTERCOM_POSITIVA_MEX503;
            } else {
                return str_replace('%error%', 'Error al recibir la solicitud', IntercomConstants::RESPUESTA_INTERCOM_NEGATIVA_MEX503);
            }
        } catch (\Exception $e) {
            Log::info('Error clase CertificadoCFECANService metodo statusObtenerCFE: ' . $e->getMessage());
            return str_replace('%error%', 'Error al recibir la solicitud', IntercomConstants::RESPUESTA_INTERCOM_NEGATIVA_MEX503);
        }
    }

    public function obtenerCFEPorNumero($parametrosSolicitud)
    {
        try {
            $listaCertificadoCFE = CertificadoCFE::where('numero_certificado', '=', $parametrosSolicitud->codigoFormato)
                ->get();
            return $listaCertificadoCFE;
        } catch (\Exception $e) {
            Log::info('Error clase CertificadoCFECANService metodo obtenerCFEPorNumero: ' . $e->getMessage());
            return [];
        }
    }

    public function obtenerArchivoPdfPorID($idCFE)
    {
        try {
            $listaCertificadoCFE = CertificadoCFE::where('id_cfe', '=', $idCFE)
                ->select('archivo_adjunto_path')
                ->get();
            return $listaCertificadoCFE;
        } catch (\Exception $e) {
            Log::info('Error clase CertificadoCFECANService metodo obtenerArchivoPdfPorID: ' . $e->getMessage());
            return [];
        }
    }

    public function obtenerCFIPoEnvioRecepcionDocumento()
    {
        try {
            $certificadoCFE = CertificadoCFE::where('envio_recepcion_documento', '=', 'Pendiente')
                ->get();
            return $certificadoCFE;
        } catch (\Exception $e) {
            Log::info('Error clase CertificadoCFECANService metodo obtenerCFIPoEnvioRecepcionDocumento: ' . $e->getMessage());
            return [];
        }
    }

    public function actualizadoCertificadoCFE($certificadoCFE)
    {
        try {
            $certificado = $certificadoCFE->save();
            return $certificado;
        } catch (\Exception $e) {
            Log::info('Error clase CertificadoCFECANService metodo actualizadoCertificadoCFE: ' . $e->getMessage());
            return $certificadoCFE;
        }
    }
}
