<?php

namespace Modules\Intercom\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErroresDocumentosRecibidos extends Model
{

    use HasFactory;

    protected $connection = 'agrocalidad';
    protected $table = 'g_interoperabilidad_can.errors_documentos_recibidos';
    protected $primaryKey = 'id_edr';
    protected $fillable = [
        'codigo_formato',
        'fecha_emision',
        'punto_origen',
        'error_presentado',
        'tipo_documento',
        'estado_envio'
    ];

    const CREATED_AT = 'fecha_registro';
    const UPDATED_AT = 'fecha_actualizacion';
}
