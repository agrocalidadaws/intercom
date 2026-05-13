<?php

namespace Modules\Intercom\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InteroperabilidadCAN extends Model
{

    use HasFactory;

    protected $connection = 'agrocalidad';
    protected $table = 'g_interoperabilidad_can.interoperabilidad_can';
    protected $primaryKey = 'id_interoperabilidad';
    protected $fillable = [
        'id_certificado_permiso',
        'codigo_fitosanitario_o',
        'codigo_fitosanitario_c',
        'tipo_documento',
        'metodo',
        'estado_documento',
        'respuesta_intercom',
        'parth_archivo',
        'ejecutado_por'
    ];
    
    const CREATED_AT = 'fecha_registro';
    const UPDATED_AT = 'fecha_actualizacion';
}
