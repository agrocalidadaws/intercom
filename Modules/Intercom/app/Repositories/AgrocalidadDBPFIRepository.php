<?php

namespace Modules\Intercom\Repositories;

use Modules\Intercom\Domain\Xml\IntercomConstants;
use Modules\Intercom\Interfaces\AgrocalidadPFIRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AgrocalidadDBPFIRepository implements AgrocalidadPFIRepository
{

    protected $connection;

    public function __construct()
    {
        $this->connection = DB::connection('agrocalidad');
    }

    public function fetchAllPhytosanitaryImportPermitOne(): \stdClass
    {

        try {
            $sql = $this->getImportPermitSQL();
            $query = $this->connection->select($sql);

            return sizeof($query) > 0 ? $query[0] : null;
        } catch (\Exception $e) {
            throw new \Exception("Error class AgrocalidadDBPFIRepository: " . $e->getMessage());
        }
    }

    public function fetchAllPhytosanitaryImportPermit(): array
    {
        try {
            $sql = $this->getImportPermitSQL();
            $query = $this->connection->select($sql);
            return $query;
        } catch (\Exception $e) {
            throw new \Exception("Error class AgrocalidadDBPFIRepository: " . $e->getMessage());
        }
    }

    public function fetchPhytosanitaryImportPermit(int $cert_id)
    {
        try {
            $sql = $this->getImportPermitSQLId($cert_id);
            $query = $this->connection->select($sql);
            return sizeof($query) > 0 ? $query[0] : null;
        } catch (\Exception $e) {
            throw new \Exception("Error: " . $e->getMessage());
        }
    }

    /**
     * @return string
     */
    private function getImportPermitSQL(): string
    {
        $canCountries = array_column(IntercomConstants::CAN_COUNTRIES, 'location_id');
        $canCountries = implode(",", $canCountries);
        $fechaInicio = IntercomConstants::FECHA_INICIO;
        $numeroConsulta = IntercomConstants::NUMERO_CONSULTA;

        $sql = 'SELECT imp.id_importacion,
        imp.id_vue,
 		to_char(imp.fecha_inicio, \'YYYY-MM-DD"T"HH24:MI:SS"Z"\') AS fecha_inicio,
        to_char(imp.fecha_creacion, \'YYYY-MM-DD"T"HH24:MI:SS"Z"\') AS fecha_creacion,
        (imp.fecha_vigencia - imp.fecha_creacion) AS dias_vigencia,
        imp.identificador_operador,
        CASE WHEN op.razon_social = \'\' THEN op.apellido_representante||\' \'||op.nombre_representante
 		    ELSE op.razon_social
 	    END AS nombre_consorcio,
 	    CASE WHEN op.direccion = \'\' THEN \'S/N\' ELSE op.direccion END AS direccion_consorcio,
 	    CASE WHEN op.parroquia = \'\' THEN \'S/N\' ELSE op.parroquia END AS parroquia_consorcio,
 	    CASE WHEN op.canton = \'\' THEN \'S/N\' ELSE op.canton END AS canton_consorcio,
 	    CASE WHEN op.provincia = \'\' THEN \'S/N\' ELSE op.provincia END AS provincia_consorcio,
 		pud.codigo_puerto AS codigo_puerto_destino,
 	    pud.nombre_puerto AS nombre_puerto_destion,
 	    pue.codigo_puerto AS codigo_puerto_embarque,
 	    pue.nombre_puerto AS nombre_puerto_embarque,
        locex.codigo as codigo_exportacion,
        loc.codigo as codigo_embarque,
        imp.pais_embarque,
 		imp.puerto_embarque,
 		imp.nombre_embarcador, 
 		imp.nombre_provincia,
 		imp.nombre_ciudad,
 		imp.puerto_destino,
 		imp.nombre_exportador,
 		imp.direccion_exportador,
 		imp.pais_exportacion,
        imp.estado,
        fem.apellido ||\' \'|| fem.nombre as nombre_tecnico 
        FROM g_importaciones.importaciones imp
            INNER JOIN g_operadores.operadores AS op ON imp.identificador_operador = op.identificador
            INNER JOIN g_catalogos.puertos AS pud ON imp.id_puerto_destino = pud.id_puerto
            INNER JOIN g_catalogos.puertos AS pue ON imp.id_puerto_embarque  = pue.id_puerto
            INNER JOIN g_catalogos.localizacion loc ON imp.id_localizacion = loc.id_localizacion
            INNER JOIN g_catalogos.localizacion locex ON imp.id_pais_exportacion = locex.id_localizacion
            INNER JOIN g_revision_solicitudes.grupos_solicitudes gso ON imp.id_importacion = gso.id_solicitud
            INNER JOIN g_revision_solicitudes.asignacion_inspector ain ON gso.id_grupo = ain.id_grupo
            INNER JOIN g_uath.ficha_empleado fem ON ain.identificador_inspector = fem.identificador
        WHERE imp.id_pais_exportacion IN (' . $canCountries . ')
            AND imp.estado = \'aprobado\' 
            AND imp.tipo_certificado = \'Permiso Fitosanitario de Importacion\'
            AND imp.fecha_creacion >= \'' . $fechaInicio . '\' 
            AND ain.tipo_solicitud = \'Importación\'
            AND ain.tipo_inspector = \'Documental\' 
            AND imp.id_importacion NOT IN (SELECT intc.id_certificado_permiso 
								FROM g_interoperabilidad_can.interoperabilidad_can intc 
						WHERE intc.tipo_documento = \'PFI\' and intc.metodo = \'M-BA002\') 
       ORDER BY imp.id_importacion DESC LIMIT ' . $numeroConsulta;
        return $sql;
    }

    private function getImportPermitSQLId($idImportacion): string
    {
        $canCountries = array_column(IntercomConstants::CAN_COUNTRIES, 'location_id');
        $canCountries = implode(",", $canCountries);
        $fechaInicio = IntercomConstants::FECHA_INICIO;
        $numeroConsulta = IntercomConstants::NUMERO_CONSULTA;

        $sql = 'SELECT imp.id_importacion,
        imp.id_vue,
 		to_char(imp.fecha_inicio, \'YYYY-MM-DD"T"HH24:MI:SS"Z"\') AS fecha_inicio,
        to_char(imp.fecha_creacion, \'YYYY-MM-DD"T"HH24:MI:SS"Z"\') AS fecha_creacion,
        (imp.fecha_vigencia - imp.fecha_creacion) AS dias_vigencia,
        imp.identificador_operador,
        CASE WHEN op.razon_social = \'\' THEN op.apellido_representante||\' \'||op.nombre_representante
 		    ELSE op.razon_social
 	    END AS nombre_consorcio,
 	    CASE WHEN op.direccion = \'\' THEN \'S/N\' ELSE op.direccion END AS direccion_consorcio,
 	    CASE WHEN op.parroquia = \'\' THEN \'S/N\' ELSE op.parroquia END AS parroquia_consorcio,
 	    CASE WHEN op.canton = \'\' THEN \'S/N\' ELSE op.canton END AS canton_consorcio,
 	    CASE WHEN op.provincia = \'\' THEN \'S/N\' ELSE op.provincia END AS provincia_consorcio,
 		pud.codigo_puerto AS codigo_puerto_destino,
 	    pud.nombre_puerto AS nombre_puerto_destion,
 	    pue.codigo_puerto AS codigo_puerto_embarque,
 	    pue.nombre_puerto AS nombre_puerto_embarque,
        locex.codigo as codigo_exportacion,
        loc.codigo as codigo_embarque,
        imp.pais_embarque,
 		imp.puerto_embarque,
 		imp.nombre_embarcador, 
 		imp.nombre_provincia,
 		imp.nombre_ciudad,
 		imp.puerto_destino,
 		imp.nombre_exportador,
 		imp.direccion_exportador,
 		imp.pais_exportacion,
        imp.estado,
        fem.apellido ||\' \'|| fem.nombre as nombre_tecnico 
        FROM g_importaciones.importaciones imp
            INNER JOIN g_operadores.operadores AS op ON imp.identificador_operador = op.identificador
            INNER JOIN g_catalogos.puertos AS pud ON imp.id_puerto_destino = pud.id_puerto
            INNER JOIN g_catalogos.puertos AS pue ON imp.id_puerto_embarque  = pue.id_puerto
            INNER JOIN g_catalogos.localizacion loc ON imp.id_localizacion = loc.id_localizacion
            INNER JOIN g_catalogos.localizacion locex ON imp.id_pais_exportacion = locex.id_localizacion
            INNER JOIN g_revision_solicitudes.grupos_solicitudes gso ON imp.id_importacion = gso.id_solicitud
            INNER JOIN g_revision_solicitudes.asignacion_inspector ain ON gso.id_grupo = ain.id_grupo
            INNER JOIN g_uath.ficha_empleado fem ON ain.identificador_inspector = fem.identificador
        WHERE imp.id_pais_exportacion IN (' . $canCountries . ')
            AND imp.estado = \'aprobado\' 
            AND imp.id_importacion = ' . $idImportacion . '  
            AND imp.tipo_certificado = \'Permiso Fitosanitario de Importacion\'
            AND imp.fecha_creacion >= \'' . $fechaInicio . '\' 
            AND ain.tipo_solicitud = \'Importación\'
            AND ain.tipo_inspector = \'Documental\'
       ORDER BY imp.id_importacion';
        return $sql;
    }

    public function fetchImportPermitProducts(int $idImportacion)
    {

        try {
            $sql = "SELECT 
                CASE 
                WHEN impr.nombre_producto_vue = '' THEN 'Ninguno' ELSE impr.nombre_producto_vue END AS nombre_producto_vue, 
                pro.nombre_comun, 
                pro.nombre_cientifico, 
                impr.peso
        FROM g_importaciones.importaciones_productos impr 
            INNER JOIN g_catalogos.productos pro ON impr.id_producto = pro.id_producto
        WHERE impr.id_importacion = ?";

            $result = $this->connection->select($sql, [$idImportacion]);

            return $result;
        } catch (\Exception $e) {
            throw new \Exception("Error class AgrocalidadDBPFIRepository: " . $e->getMessage());
        }
    }
}
