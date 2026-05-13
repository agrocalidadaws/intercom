<?php

namespace Modules\Intercom\Repositories;

use Illuminate\Support\Arr;

use Modules\Intercom\Domain\Xml\IntercomConstants;
use Modules\Intercom\Interfaces\AgrocalidadCFERepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AgrocalidadDBCFERepository implements AgrocalidadCFERepository
{
	protected $connection;

	public function __construct()
	{
		$this->connection = DB::connection('agrocalidad');
	}

	public function fetchAllExportPhytosanitaryCertificates()
	{
		try {
			$sql = $this->getExportPhytosanitarySQL();
			$query = $this->connection->select($sql);
			//$query = json_decode(json_encode($query), true);
			return $query;
		} catch (\Exception $e) {
			throw new \Exception("Error Class AgrocalidadDBCFERepository: " . $e->getMessage());
		}
	}

	public function fetchExportPhytosanitaryCertificate($cert_id): \stdClass
	{
		try {
			$sql = $this->getExportPhytosanitarySQLPorId();
			$sql .= "\n WHERE cc.id_certificado_fitosanitario = ?";
			$result = $this->connection->select($sql, [$cert_id]);
			return sizeof($result) > 0 ? $result[0] : null;
		} catch (\Exception $e) {
			throw new \Exception("Error Class AgrocalidadDBCFERepository: " . $e->getMessage());
		}
	}

	/**
	 * @return string
	 */
	private function getExportPhytosanitarySQL(): string
	{
		$canCountries = array_column(IntercomConstants::CAN_COUNTRIES, 'location_id');
		$canCountries = implode(",", $canCountries);
		$fechaInicio = IntercomConstants::FECHA_INICIO;
		$limiteConsulta = IntercomConstants::NUMERO_CONSULTA;

		$sql = <<<SQL
			/*NBL*/WITH cte_certificado AS (
				SELECT
					cf.id_certificado_fitosanitario,
					cf.codigo_certificado,
					cf.identificador_solicitante,
					cf.codigo_certificado_importacion,
					CASE
						WHEN cf.tipo_solicitud IN ('musaceas', 'otros') THEN to_char(cf.fecha_embarque, 'YYYY-MM-DD"T"HH24:MI:SS"Z"')
						ELSE  to_char(cf.fecha_aprobacion_certificado, 'YYYY-MM-DD"T"HH24:MI:SS"Z"')
					END AS fecha_aprobacion_certificado_letras,
					cf.id_idioma,
					to_char(cf.fecha_aprobacion_certificado, 'YYYY-MM-DD"T"HH24:MI:SS"Z"') as fecha_aprobacion_certificado,
					cf.estado_certificado,
					cf.id_tipo_produccion,
					cf.id_pais_origen,
					cf.id_medio_transporte,
					cf.id_puerto_embarque,
					cf.id_provincia_puerto_embarque,
					cf.nombre_marca,
					CASE
						WHEN cf.informacion_adicional = '' THEN 'Sin información'
						ELSE cf.informacion_adicional
					END AS informacion_adicional,
					cf.codigo_certificado_importacion,
					cf.nombre_consignatario,
					cf.direccion_consignatario,
					cf.forma_pago,
					cf.descuento,
					cf.motivo_descuento,
					cf.es_reemplazo,
					cf.motivo_reemplazo,
					cf.id_certificado_reemplazo,
					to_char(cf.fecha_revision, 'YYYY-MM-DD"T"HH24:MI:SS"Z"') AS fecha_inspeccion,
					cf.identificador_revision,
					cf.observacion_revision,
					cf.motivo_desestimiento,
					to_char(cf.fecha_creacion_certificado, 'YYYY-MM-DD"T"HH24:MI:SS"Z"') AS fecha_fin_vigencia,
					cf.certificado,
					cf.estado_ephyto,
					cf.observacion_ephyto,
					cf.fecha_anulacion_certificado,
					cf.fecha_reemplazo_certificado,
					cf_pais_des.id_pais_destino,
					cf_pais_des.nombre_pais_destino ,
					cf_pais_des.id_puerto_destino ,
					cf_pais_des.nombre_puerto_destino,
					lo.nombre as nombre_pais_destino,
					lo.codigo as codigo_pais_detino,
					fe.apellido ||' '||fe.nombre as nombre,
					fe.identificador,
					dc.provincia,
					dc.nombre_puesto,
					dc.oficina,
					lopo.nombre as pais_origen,
					lopo.codigo as codigo_pais_origen,
					cf_pue_destin.id_pais_destino as puerto_id_pais_destino,
					cf_pue_destin.id_puerto_destino as puerto_id_puerto_destino
				FROM
					g_certificado_fitosanitario.certificado_fitosanitario cf
					INNER JOIN g_certificado_fitosanitario.paises_puertos_destino cf_pais_des
					ON cf.id_certificado_fitosanitario = cf_pais_des.id_certificado_fitosanitario
					INNER JOIN   g_catalogos.localizacion lo
					ON cf_pais_des.id_pais_destino=lo.id_localizacion
					INNER JOIN g_uath.ficha_empleado fe
					ON fe.identificador=cf.identificador_revision
					INNER JOIN g_uath.datos_contrato dc
					ON fe.identificador = dc.identificador
					INNER JOIN   g_catalogos.localizacion lopo
					ON cf.id_pais_origen=lopo.id_localizacion
					INNER JOIN g_certificado_fitosanitario.paises_puertos_destino cf_pue_destin
					ON cf.id_certificado_fitosanitario=cf_pue_destin.id_certificado_fitosanitario
				WHERE
					estado_certificado IN ('Aprobado')
					AND cf_pais_des.id_pais_destino IN ($canCountries)
					AND fe.estado_empleado = 'activo'
					AND dc.estado = '1'
					AND cf.fecha_creacion_certificado >= '{$fechaInicio}'
					AND cf.id_certificado_fitosanitario 
						NOT IN (SELECT intc.id_certificado_permiso 
								FROM g_interoperabilidad_can.interoperabilidad_can intc 
						WHERE intc.tipo_documento = 'CFE' AND intc.metodo = 'M-BA002')
				ORDER BY cf.fecha_creacion_certificado ASC limit $limiteConsulta), cte_informacion_adicional AS (
				SELECT
					tr.id_certificado_fitosanitario,
					regexp_replace(STRING_AGG(informacion_adicional, ', '), E'[\\n\\r]+', ' ', 'g') AS informacion_adicional
				FROM
					(
						SELECT
							cfp.id_certificado_fitosanitario,
							COALESCE(
        concat_ws(
            ', ',
            STRING_AGG(DISTINCT trf.informacion_adicional, ', '),
            STRING_AGG(DISTINCT trt.informacion_adicional, ', ')
        ),
        'N/A'
    ) AS informacion_adicional
						FROM
							g_certificado_fitosanitario.certificado_fitosanitario_productos cfp
							INNER JOIN (
								SELECT
									cfp.id_certificado_fitosanitario,
									ep.id_certificado_fitosanitario_producto,
									p.nombre_comun || ' / ' || p.nombre_cientifico AS nombre_producto,
									COALESCE(STRING_AGG(DISTINCT (r.detalle_impreso), ', '), 'N/A') AS informacion_adicional
								FROM
									g_certificado_fitosanitario.certificado_fitosanitario cfp
									INNER JOIN g_certificado_fitosanitario.paises_puertos_destino ppd ON ppd.id_certificado_fitosanitario = cfp.id_certificado_fitosanitario
									INNER JOIN g_certificado_fitosanitario.certificado_fitosanitario_productos ep ON cfp.id_certificado_fitosanitario = ep.id_certificado_fitosanitario
									INNER JOIN g_catalogos.productos p ON ep.id_producto = p.id_producto
									INNER JOIN g_requisitos.requisitos_comercializacion rc ON rc.id_producto = ep.id_producto
									INNER JOIN g_requisitos.requisitos_asignados ra ON rc.id_requisito_comercio = ra.id_requisito_comercio
									INNER JOIN g_requisitos.requisitos r ON ra.requisito = r.id_requisito
								WHERE
									rc.id_localizacion = ppd.id_pais_destino
									AND ra.tipo = 'Exportación'
									AND r.tipo = 'Exportación'
									AND r.estado = 1
								GROUP BY
									cfp.id_certificado_fitosanitario,
									p.nombre_comun,
									p.nombre_cientifico,
									ep.id_certificado_fitosanitario_producto
							) trf ON trf.id_certificado_fitosanitario_producto = cfp.id_certificado_fitosanitario_producto
							LEFT JOIN (
								SELECT
									cfp.id_certificado_fitosanitario,
									ep.id_certificado_fitosanitario_producto,
									p.nombre_comun || ' / ' || p.nombre_cientifico AS nombre_producto,
									COALESCE(STRING_AGG(DISTINCT (r.detalle_impreso), ', '), 'N/A') AS informacion_adicional
								FROM
									g_certificado_fitosanitario.certificado_fitosanitario cfp
									INNER JOIN g_certificado_fitosanitario.paises_puertos_transito ppt ON ppt.id_certificado_fitosanitario = cfp.id_certificado_fitosanitario
									INNER JOIN g_certificado_fitosanitario.certificado_fitosanitario_productos ep ON cfp.id_certificado_fitosanitario = ep.id_certificado_fitosanitario
									INNER JOIN g_catalogos.productos p ON ep.id_producto = p.id_producto
									INNER JOIN g_requisitos.requisitos_comercializacion rc ON rc.id_producto = ep.id_producto
									INNER JOIN g_requisitos.requisitos_asignados ra ON rc.id_requisito_comercio = ra.id_requisito_comercio
									INNER JOIN g_requisitos.requisitos r ON ra.requisito = r.id_requisito
								WHERE
									rc.id_localizacion = ppt.id_pais_transito
									AND ra.tipo = 'Tránsito'
									AND r.tipo = 'Tránsito'
									AND r.estado = 1
								GROUP BY
									cfp.id_certificado_fitosanitario,
									p.nombre_comun,
									p.nombre_cientifico,
									ep.id_certificado_fitosanitario_producto
							) trt ON trt.id_certificado_fitosanitario_producto = cfp.id_certificado_fitosanitario_producto
						GROUP BY
							cfp.id_certificado_fitosanitario,
							trf.informacion_adicional
					) tr
				GROUP BY
					tr.id_certificado_fitosanitario
			)
			SELECT
				cc.*,
				cia.informacion_adicional,
				puer.codigo_puerto,
			puer.nombre_puerto,
			med_trans.codigo_hub,
			med_trans.tipo
			FROM
				cte_certificado cc
				INNER JOIN cte_informacion_adicional cia
				ON cc.id_certificado_fitosanitario = cia.id_certificado_fitosanitario
				INNER JOIN g_catalogos.localizacion lo_puerto
				ON cc.puerto_id_pais_destino= lo_puerto.id_localizacion
				INNER JOIN  g_catalogos.puertos puer
				ON cc.puerto_id_puerto_destino= puer.id_puerto
				INNER JOIN g_catalogos.medios_transporte med_trans
				ON med_trans.id_medios_transporte=cc.id_medio_transporte
SQL;
		return $sql;
	}

	/**
	 * @return string
	 */
	private function getExportPhytosanitarySQLPorId(): string
	{
		$canCountries = array_column(IntercomConstants::CAN_COUNTRIES, 'location_id');
		$canCountries = implode(",", $canCountries);
		$fechaInicio = IntercomConstants::FECHA_INICIO;

		$sql = <<<SQL
			/*NBL*/WITH cte_certificado AS (
				SELECT
					cf.id_certificado_fitosanitario,
					cf.codigo_certificado,
					cf.identificador_solicitante,
					cf.codigo_certificado_importacion,
					CASE
						WHEN cf.tipo_solicitud IN ('musaceas', 'otros') THEN to_char(cf.fecha_embarque, 'YYYY-MM-DD"T"HH24:MI:SS"Z"')
						ELSE  to_char(cf.fecha_aprobacion_certificado, 'YYYY-MM-DD"T"HH24:MI:SS"Z"')
					END AS fecha_aprobacion_certificado_letras,
					cf.id_idioma,
					to_char(cf.fecha_aprobacion_certificado, 'YYYY-MM-DD"T"HH24:MI:SS"Z"') as fecha_aprobacion_certificado,
					cf.estado_certificado,
					cf.id_tipo_produccion,
					cf.id_pais_origen,
					cf.id_medio_transporte,
					cf.id_puerto_embarque,
					cf.id_provincia_puerto_embarque,
					cf.nombre_marca,
					CASE
						WHEN cf.informacion_adicional = '' THEN 'Sin información'
						ELSE cf.informacion_adicional
					END AS informacion_adicional,
					cf.codigo_certificado_importacion,
					cf.nombre_consignatario,
					cf.direccion_consignatario,
					cf.forma_pago,
					cf.descuento,
					cf.motivo_descuento,
					cf.es_reemplazo,
					cf.motivo_reemplazo,
					cf.id_certificado_reemplazo,
					to_char(cf.fecha_revision, 'YYYY-MM-DD"T"HH24:MI:SS"Z"') AS fecha_inspeccion,
					cf.identificador_revision,
					cf.observacion_revision,
					cf.motivo_desestimiento,
					to_char(cf.fecha_creacion_certificado, 'YYYY-MM-DD"T"HH24:MI:SS"Z"') AS fecha_fin_vigencia,
					cf.certificado,
					cf.estado_ephyto,
					cf.observacion_ephyto,
					cf.fecha_anulacion_certificado,
					cf.fecha_reemplazo_certificado,
					cf_pais_des.id_pais_destino,
					cf_pais_des.nombre_pais_destino ,
					cf_pais_des.id_puerto_destino ,
					cf_pais_des.nombre_puerto_destino,
					lo.nombre as nombre_pais_destino,
					lo.codigo as codigo_pais_detino,
					fe.apellido ||' '||fe.nombre as nombre,
					fe.identificador,
					dc.provincia,
					dc.nombre_puesto,
					dc.oficina,
					lopo.nombre as pais_origen,
					lopo.codigo as codigo_pais_origen,
					cf_pue_destin.id_pais_destino as puerto_id_pais_destino,
					cf_pue_destin.id_puerto_destino as puerto_id_puerto_destino
				FROM
					g_certificado_fitosanitario.certificado_fitosanitario cf
					INNER JOIN g_certificado_fitosanitario.paises_puertos_destino cf_pais_des
					ON cf.id_certificado_fitosanitario = cf_pais_des.id_certificado_fitosanitario
					INNER JOIN   g_catalogos.localizacion lo
					ON cf_pais_des.id_pais_destino=lo.id_localizacion
					INNER JOIN g_uath.ficha_empleado fe
					ON fe.identificador=cf.identificador_revision
					INNER JOIN g_uath.datos_contrato dc
					ON fe.identificador = dc.identificador
					INNER JOIN   g_catalogos.localizacion lopo
					ON cf.id_pais_origen=lopo.id_localizacion
					INNER JOIN g_certificado_fitosanitario.paises_puertos_destino cf_pue_destin
					ON cf.id_certificado_fitosanitario=cf_pue_destin.id_certificado_fitosanitario
				WHERE
					estado_certificado IN ('Aprobado')
					AND cf_pais_des.id_pais_destino IN ($canCountries)
					AND fe.estado_empleado = 'activo'
					AND dc.estado = '1'
					AND cf.fecha_creacion_certificado >= '{$fechaInicio}'
				ORDER BY cf.fecha_creacion_certificado), cte_informacion_adicional AS (
				SELECT
					tr.id_certificado_fitosanitario,
					regexp_replace(STRING_AGG(informacion_adicional, ', '), E'[\\n\\r]+', ' ', 'g') AS informacion_adicional
				FROM
					(
						SELECT
							cfp.id_certificado_fitosanitario,
							COALESCE(
        concat_ws(
            ', ',
            STRING_AGG(DISTINCT trf.informacion_adicional, ', '),
            STRING_AGG(DISTINCT trt.informacion_adicional, ', ')
        ),
        'N/A'
    ) AS informacion_adicional
						FROM
							g_certificado_fitosanitario.certificado_fitosanitario_productos cfp
							INNER JOIN (
								SELECT
									cfp.id_certificado_fitosanitario,
									ep.id_certificado_fitosanitario_producto,
									p.nombre_comun || ' / ' || p.nombre_cientifico AS nombre_producto,
									COALESCE(STRING_AGG(DISTINCT (r.detalle_impreso), ', '), 'N/A') AS informacion_adicional
								FROM
									g_certificado_fitosanitario.certificado_fitosanitario cfp
									INNER JOIN g_certificado_fitosanitario.paises_puertos_destino ppd ON ppd.id_certificado_fitosanitario = cfp.id_certificado_fitosanitario
									INNER JOIN g_certificado_fitosanitario.certificado_fitosanitario_productos ep ON cfp.id_certificado_fitosanitario = ep.id_certificado_fitosanitario
									INNER JOIN g_catalogos.productos p ON ep.id_producto = p.id_producto
									INNER JOIN g_requisitos.requisitos_comercializacion rc ON rc.id_producto = ep.id_producto
									INNER JOIN g_requisitos.requisitos_asignados ra ON rc.id_requisito_comercio = ra.id_requisito_comercio
									INNER JOIN g_requisitos.requisitos r ON ra.requisito = r.id_requisito
								WHERE
									rc.id_localizacion = ppd.id_pais_destino
									AND ra.tipo = 'Exportación'
									AND r.tipo = 'Exportación'
									AND r.estado = 1
								GROUP BY
									cfp.id_certificado_fitosanitario,
									p.nombre_comun,
									p.nombre_cientifico,
									ep.id_certificado_fitosanitario_producto
							) trf ON trf.id_certificado_fitosanitario_producto = cfp.id_certificado_fitosanitario_producto
							LEFT JOIN (
								SELECT
									cfp.id_certificado_fitosanitario,
									ep.id_certificado_fitosanitario_producto,
									p.nombre_comun || ' / ' || p.nombre_cientifico AS nombre_producto,
									COALESCE(STRING_AGG(DISTINCT (r.detalle_impreso), ', '), 'N/A') AS informacion_adicional
								FROM
									g_certificado_fitosanitario.certificado_fitosanitario cfp
									INNER JOIN g_certificado_fitosanitario.paises_puertos_transito ppt ON ppt.id_certificado_fitosanitario = cfp.id_certificado_fitosanitario
									INNER JOIN g_certificado_fitosanitario.certificado_fitosanitario_productos ep ON cfp.id_certificado_fitosanitario = ep.id_certificado_fitosanitario
									INNER JOIN g_catalogos.productos p ON ep.id_producto = p.id_producto
									INNER JOIN g_requisitos.requisitos_comercializacion rc ON rc.id_producto = ep.id_producto
									INNER JOIN g_requisitos.requisitos_asignados ra ON rc.id_requisito_comercio = ra.id_requisito_comercio
									INNER JOIN g_requisitos.requisitos r ON ra.requisito = r.id_requisito
								WHERE
									rc.id_localizacion = ppt.id_pais_transito
									AND ra.tipo = 'Tránsito'
									AND r.tipo = 'Tránsito'
									AND r.estado = 1
								GROUP BY
									cfp.id_certificado_fitosanitario,
									p.nombre_comun,
									p.nombre_cientifico,
									ep.id_certificado_fitosanitario_producto
							) trt ON trt.id_certificado_fitosanitario_producto = cfp.id_certificado_fitosanitario_producto
						GROUP BY
							cfp.id_certificado_fitosanitario,
							trf.informacion_adicional
					) tr
				GROUP BY
					tr.id_certificado_fitosanitario
			)
			SELECT
				cc.*,
				cia.informacion_adicional,
				puer.codigo_puerto,
			puer.nombre_puerto,
			med_trans.codigo_hub,
			med_trans.tipo
			FROM
				cte_certificado cc
				INNER JOIN cte_informacion_adicional cia
				ON cc.id_certificado_fitosanitario = cia.id_certificado_fitosanitario
				INNER JOIN g_catalogos.localizacion lo_puerto
				ON cc.puerto_id_pais_destino= lo_puerto.id_localizacion
				INNER JOIN  g_catalogos.puertos puer
				ON cc.puerto_id_puerto_destino= puer.id_puerto
				INNER JOIN g_catalogos.medios_transporte med_trans
				ON med_trans.id_medios_transporte=cc.id_medio_transporte
SQL;
		return $sql;
	}

	public function fetchCertificateProducts(int $phytoSanitaryCertificateId)
	{
		$sql = <<<SQL
            /*NBL*/SELECT jsonb_agg(productos_operador) AS resultado
                FROM (
                    SELECT jsonb_build_object(
                        'productos_operador', jsonb_build_object(
                            'datos_operador', jsonb_build_object(
                                'identificador_operador', op.identificador,
                                'nombre_operador', CASE
                                                        WHEN op.razon_social = ''
                                                        THEN op.nombre_representante || ' ' || op.apellido_representante
                                                        ELSE op.razon_social
                                                    END,
                                'direccion_operador', op.direccion
                            ),
                            'productos', (
                                SELECT jsonb_agg(
                                    jsonb_build_object(
                                        'id_certificado_fitosanitario_producto', cf.id_certificado_fitosanitario_producto,
                                        'id_certificado_fitosanitario', cf.id_certificado_fitosanitario,
                                        'id_total_inspeccion_fitosanitaria', cf.id_total_inspeccion_fitosanitaria,
                                        'identificador_exportador', cf.identificador_exportador,
                                        'id_subtipo_producto', cf.id_subtipo_producto,
                                        'nombre_subtipo_producto', cf.nombre_subtipo_producto,
                                        'id_producto', cf.id_producto,
                                        'nombre_producto', cf.nombre_producto,
                                        'cantidad_comercial', cf.cantidad_comercial,
                                        'id_unidad_cantidad_comercial', cf.id_unidad_cantidad_comercial,
                                        'codigo_unidad_cantidad_comercial', cf.codigo_unidad_cantidad_comercial,
                                        'peso_neto', cf.peso_neto,
                                        'id_unidad_peso_neto', cf.id_unidad_peso_neto,
                                        'codigo_unidad_peso_neto', cf.codigo_unidad_peso_neto,
                                        'peso_bruto', cf.peso_bruto,
                                        'id_unidad_peso_bruto', cf.id_unidad_peso_bruto,
                                        'codigo_unidad_peso_bruto', cf.codigo_unidad_peso_bruto,
                                        'id_tipo_tratamiento', cf.id_tipo_tratamiento,
                                        'codigo_tipo_tratamiento', cf.codigo_tipo_tratamiento,
                                        'id_tratamiento', cf.id_tratamiento,
                                        'codigo_tratamiento', cf.codigo_tratamiento,
                                        'id_duracion', cf.id_duracion,
                                        'codigo_unidad_duracion', cf.codigo_unidad_duracion,
                                        'duracion', cf.duracion,
                                        'id_temperatura', cf.id_temperatura,
                                        'codigo_unidad_temperatura', cf.codigo_unidad_temperatura,
                                        'temperatura', cf.temperatura,
                                        'fecha_tratamiento', to_char(cf.fecha_tratamiento, 'YYYY-MM-DD"T"HH24:MI:SS"Z"') ,
                                        'producto_quimico', cf.producto_quimico,
                                        'id_concentracion', cf.id_concentracion,
                                        'fecha_inspeccion', to_char(cf.fecha_inspeccion, 'YYYY-MM-DD"T"HH24:MI:SS"Z"') ,
                                        'codigo_unidad_concentracion', cf.codigo_unidad_concentracion,
                                        'concentracion', cf.concentracion,
                                        'codigo_poa', cf_prod.codigo_poa
                                    )
                                )
                                FROM g_certificado_fitosanitario.certificado_fitosanitario_productos cf
                                WHERE cf.identificador_exportador = op.identificador
                                AND cf.id_certificado_fitosanitario = ?
                            )
                        )
                    ) AS productos_operador
            FROM g_certificado_fitosanitario.certificado_fitosanitario_productos cf_prod
            INNER JOIN g_operadores.operadores op ON cf_prod.identificador_exportador = op.identificador
            WHERE cf_prod.id_certificado_fitosanitario = ? 
            GROUP BY op.identificador, op.razon_social, op.nombre_representante, op.apellido_representante, op.direccion,cf_prod.codigo_poa
            ) as consulta_interna;
        SQL;

		$result = $this->connection->select($sql, [$phytoSanitaryCertificateId, $phytoSanitaryCertificateId]);

		$operatorProducts = [];

		if (sizeof($result) > 0) {
			$operatorProducts = json_decode($result[0]->resultado);
		}

		return $operatorProducts;
	}

	public function fetchProduct(int $productId)
	{
		try {
			$sql = "SELECT nombre_comun, nombre_cientifico FROM g_catalogos.productos WHERE id_producto = ?";
			$result = $this->connection->select($sql, [$productId]);
			return sizeof($result) > 0 ? $result[0] : NULL;
		} catch (\Exception $e) {
			throw new \Exception("Error class AgrocalidadDBCFERepository: " . $e->getMessage());
		}
	}
}
