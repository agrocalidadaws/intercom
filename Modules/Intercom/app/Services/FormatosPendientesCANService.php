<?php

namespace Modules\Intercom\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Modules\Intercom\Models\FormatosPendientes;

class FormatosPendientesCANService
{

    public function guardarListaFormatosPendientes($formatosPendientesDTO)
    {

        DB::beginTransaction();

        try {

            $formatoPendientes = FormatosPendientes::create([
                'funcion' => $formatosPendientesDTO->funcion,
                'tipo_codigo' => $formatosPendientesDTO->tipoCodigo,
                'tipo_formato' => $formatosPendientesDTO->tipoFormato,
                'numero_pagina' => $formatosPendientesDTO->numeroPagina,
                'total_pagina' => $formatosPendientesDTO->totalPagina,
                'total_registros' => $formatosPendientesDTO->totalRegistro,
                'tamano_pagina' => $formatosPendientesDTO->tamanoPagina
            ]);

            $listaFormatosPendientes = $formatosPendientesDTO->listaFormatoPendiente;
            $formatosPendientesList = [];

            foreach ($listaFormatosPendientes as $formatoPendiente) {
                $formatosPendien = [
                    'id_solicitud' => $formatoPendiente->idSolicitud,
                    'id_formato' => $formatoPendiente->idFormato,
                    'codigo_formato' => $formatoPendiente->codigoFormato,
                    'punto_origen' => $formatoPendiente->puntoOrigen,
                    'fecha_envio' => $formatoPendiente->fechaEnvio,
                    'estado_documentos' => $formatoPendiente->estadoDocumentos,
                    'fecha_recepcion' => $formatoPendiente->fechaRecepcion
                ];
                $formatosPendientesList[] = $formatosPendien;
            }

            $formatoPendientes->listadoFormatosPendientes()->createMany($formatosPendientesList);
            DB::commit();
            return [
                'status' => 200,
                'data'   => 'Ok, Datos Guardados Correctamente',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return '<Response><Function>11</Function><TypeCode>M-EX501</TypeCode><Status><NameCode>63</NameCode><StatementDescription>Error:' . $e->getMessage() . '</StatementDescription></Status></Response>';
        }
    }
}
