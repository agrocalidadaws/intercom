<?php

namespace Modules\Intercom\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ListadoFormatosPendientes extends Model
{

    use HasFactory;

    protected $connection = 'agrocalidad';
    protected $table = 'g_interoperabilidad_can.listado_formatos_pendiente';
    protected $primaryKey = 'id_lfpendiente';

    protected $fillable = [
        'id_fpendiente',
        'id_solicitud',
        'id_formato',
        'codigo_formato',
        'punto_origen',
        'fecha_envio',
        'estado_documentos',
        'fecha_recepcion'
    ];

    public $timestamps = false;

    public function formatosPendientes() {
        return $this->belongsTo(FormatosPendientes::class, 'id_fpendiente', 'id_fpendiente');
    }

}
