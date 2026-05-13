<?php

namespace Modules\Intercom\Services;

use Modules\Intercom\Models\pfi\PermisoPFI;
use Illuminate\Support\Facades\DB;
use Modules\Intercom\Domain\Xml\IntercomConstants;
use Illuminate\Support\Facades\Log;

class PermisoPFICANService
{

    public function guardarPermisoPFICan($permisoPFIDTO)
    {
        DB::beginTransaction();
        try {

            $permiso = PermisoPFI::create(
                [
                    'numero_permiso' => $permisoPFIDTO->numeroPermiso,
                    'estado_cambio' => $permisoPFIDTO->estadoCambio,
                    'fecha_emision' => $permisoPFIDTO->fechaEmision,
                    'dias_vigencia' => $permisoPFIDTO->diasVigencia,
                    'proteccion_fitosanitaria' => $permisoPFIDTO->proteccionFitosanitaria,
                    'nombre_funcionario' => $permisoPFIDTO->nombreFuncionario,
                    'documento_fecha_emision' => $permisoPFIDTO->documentoFechaEmision,
                    'referencia_original_emision' => $permisoPFIDTO->referennciaOriginalEmision,
                    'numero_fitosanitario_original' => $permisoPFIDTO->numeroFitosanitarioOriginal,
                    'archivo_adjunto_path' => $permisoPFIDTO->archivoAdjuntoPath,
                    'descripcion_documento' => $permisoPFIDTO->descripcionDocumento,
                    'lugar_emision' => $permisoPFIDTO->lugarEmision,
                    'medio_transporte' => $permisoPFIDTO->medioTransporte,
                    'modo_transporte' => $permisoPFIDTO->modoTransporte,
                    'nombre_trasporte' => $permisoPFIDTO->nombreTrasporte,
                    'numero_sello' => $permisoPFIDTO->numeroSello,
                    'punto_origen' => $permisoPFIDTO->puntoOrigen,
                    'envio_recepcion_documento' => $permisoPFIDTO->envioRecepcionDocumento
                ]
            );

            $informacionAdicionalPFIDTO = $permisoPFIDTO->informacionAdicionalPFI;
            $informacionAdicionalPFIList = [];

            foreach ($informacionAdicionalPFIDTO as $informacionAdicional) {
                $informacionAdicionalPFI = [
                    'descripcion' => $informacionAdicional->subject,
                    'contenido' => $informacionAdicional->contenido
                ];
                $informacionAdicionalPFIList[] = $informacionAdicionalPFI;
            }

            $permiso->informacionAdicionalPFI()->createMany($informacionAdicionalPFIList);

            $envioRecepcionPFIDTO = $permisoPFIDTO->envioRecepcionPFI;
            $envioRecepcionPFIList = [];

            foreach ($envioRecepcionPFIDTO as $envioRecepcion) {
                $envioRecepcionPFI = [
                    'nombre_consigna' => $envioRecepcion->nombreCosigna,
                    'direccion_consignador_uno' => $envioRecepcion->direccionLineOne,
                    'direccion_consignador_dos' => $envioRecepcion->direccionLineTwo,
                    'direccion_consignador_tres' => $envioRecepcion->direccionLineThree,
                    'direccion_consignador_cuatro' => $envioRecepcion->direccionLineFour,
                    'direccion_consignador_cinco' => $envioRecepcion->direccionLineFive,
                    'tipo' => $envioRecepcion->tipo
                ];
                $envioRecepcionPFIList[] = $envioRecepcionPFI;
            }

            $permiso->envioRecepcionPFI()->createMany($envioRecepcionPFIList);

            $paisesInterrelacionadosPFIDTO = $permisoPFIDTO->paisesInterrelacionadosPFI;
            $paisesInterrelacionadosPFIList = [];

            foreach ($paisesInterrelacionadosPFIDTO as $paisesInterrelacionados) {
                $paisesInterrelacionadosPFI = [
                    'id_pais' => $paisesInterrelacionados->idPais,
                    'nombre' => $paisesInterrelacionados->nombre,
                    'tipo' => $paisesInterrelacionados->tipo,
                ];
                $paisesInterrelacionadosPFIList[] =  $paisesInterrelacionadosPFI;
            }

            $permiso->paisesInterrelacionadosPFI()->createMany($paisesInterrelacionadosPFIList);

            $productosPermisoPFIDTO = $permisoPFIDTO->productosCertificadosPFI;
            foreach ($productosPermisoPFIDTO as $productosPermiso) {

                $productosPermisoPFI = $permiso->productosPersmisoPFI()->createMany([[
                    'descripcion' => $productosPermiso->descripcion,
                    'nombre_comun' => $productosPermiso->nombreComun,
                    'nombre_cientifico' => $productosPermiso->nombreCientifico,
                    'producto_ippc' => $productosPermiso->productoIPPC,
                    'peso_neto' => $productosPermiso->pesoNeto,
                    'peso_bruto' => $productosPermiso->pesoBruto,
                    'volumen_neto' => $productosPermiso->volumenNeto,
                    'volumen_bruto' => $productosPermiso->volumenBruto,
                    'pais_origen_id' => $productosPermiso->paisOrigenId,
                    'nombre_pais' => $productosPermiso->nombrePais,
                    'nombre_zona_dentro_po' => $productosPermiso->nombreZonaDentroPO
                ]]);

                $productosInformacionAdicionalPFIDTO = $productosPermiso->productosInformacionAdicionalPFI;
                $productosInformacionAdicionalPFIList = [];

                foreach ($productosInformacionAdicionalPFIDTO as $productosInformacionAdicional) {
                    $productosInformacionAdicionalPFI = [
                        'pinfa_descripcion' => $productosInformacionAdicional->pinfaSubject,
                        'pinfa_contenido' => $productosInformacionAdicional->pinfaContenido
                    ];

                    $productosInformacionAdicionalPFIList[] = $productosInformacionAdicionalPFI;
                }

                $productosPermisoPFI[0]->productosInformacionAdicionalPFI()->createMany($productosInformacionAdicionalPFIList);

                $productosClasesPFIDTO = $productosPermiso->productosClasesPFI;
                $productosClasesPFIList = [];

                foreach ($productosClasesPFIDTO as $productosClases) {
                    $productosClasesPFI = [
                        'nombre_sistema' => $productosClases->systemName,
                        'clase_codigo' => $productosClases->classeCodigo
                    ];
                    $productosClasesPFIList[] = $productosClasesPFI;
                }

                $productosPermisoPFI[0]->productosClasesPFI()->createMany($productosClasesPFIList);

                $productoDescripcionPaquetePFIDTO = $productosPermiso->productoDescripcionPaquetePFI;
                $productoDescripcionPaquetePFIList = [];

                foreach ($productoDescripcionPaquetePFIDTO as $productoDescripcionPaqueteP) {
                    $productoDescripcionPaquetePFI = [
                        'codigo_nivel_embalaje' => $productoDescripcionPaqueteP->codigoNivelEmbalaje,
                        'cofigo_tipo_paquete' => $productoDescripcionPaqueteP->cofigoTipoPaquete,
                        'numero_paquetes' => $productoDescripcionPaqueteP->numeroPaquetes
                    ];
                    $productoDescripcionPaquetePFIList[] = $productoDescripcionPaquetePFI;
                }

                $productosPermisoPFI[0]->productoDescripcionPaquetePFI()->createMany($productoDescripcionPaquetePFIList);
            }

            DB::commit();
            return $permiso;
        } catch (\Exception $e) {
            Log::info('Error clase PermisoPFICANService metodo guardarPermisoPFICan: ' . $e->getMessage());
            DB::rollBack();
            return str_replace('%error%', 'Error: '. $e->getMessage(), IntercomConstants::RESPUESTA_INTERCOM_NEGATIVA);
        }
    }

    public function actualizarPermiso($permisoPFI)
    {
        try {
            $permiso = $permisoPFI->save();
            return $permiso;
        } catch (\Exception $e) {
            Log::info('Error clase PermisoPFICANService metodo actualizarPermiso: ' . $e->getMessage());
            return $permisoPFI;
        }
    }

    public function statusObtenerPFI($entradaBusqueda)
    {
        try {
            $permiso = PermisoPFI::where('numero_permiso', '=',  $entradaBusqueda->codigoFormato)
                ->where('punto_origen', '=', $entradaBusqueda->puntoDestino)
                ->get();

            if (!$permiso->isEmpty()) {
                return IntercomConstants::RESPUESTA_INTERCOM_POSITIVA_MEX503;
            } else {
                return str_replace('%error%', 'Error al recibir la solicitud', IntercomConstants::RESPUESTA_INTERCOM_NEGATIVA_MEX503);
            }
        } catch (\Exception $e) {
            Log::info('Error clase PermisoPFICANService metodo statusObtenerPFI: ' . $e->getMessage());
            return str_replace('%error%', 'Error: '. $e->getMessage(), IntercomConstants::RESPUESTA_INTERCOM_NEGATIVA_MEX503);;
        }
    }

    public function obtenerPFIPorNumero($parametrosSolicitud)
    {
        try {
            $listaPermisoPfi = PermisoPFI::where('numero_permiso', '=', $parametrosSolicitud->codigoFormato)
                ->get();
            return $listaPermisoPfi;
        } catch (\Exception $e) {
            Log::info('Error clase PermisoPFICANService metodo obtenerPFIPorNumero: ' . $e->getMessage());
            return [];
        }
    }

    public function obtenerCFIPoEnvioRecepcionDocumento()
    {
        try {
            $permisoPFI = PermisoPFI::where('envio_recepcion_documento', '=', 'Pendiente')->get();
            return $permisoPFI;
        } catch (\Exception $e) {
            Log::info('Error clase PermisoPFICANService metodo obtenerCFIPoEnvioRecepcionDocumento: ' . $e->getMessage());
            return [];
        }
    }

    public function obtenerArvhivoPfd($idPfi)
    {
        try {
            $listaPermisoPfi = PermisoPFI::where('id_pfi', '=', $idPfi)
                ->select('archivo_adjunto_path')
                ->get();

            return $listaPermisoPfi;
        } catch (\Exception $e) {
            Log::info('Error clase PermisoPFICANService metodo obtenerArvhivoPfd: ' . $e->getMessage());
            return [];
        }
    }
}
