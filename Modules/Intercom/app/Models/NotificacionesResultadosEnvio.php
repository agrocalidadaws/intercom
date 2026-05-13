<?php

namespace Modules\Intercom\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificacionesResultadosEnvio extends Model {

    use HasFactory;

    protected $connection = 'agrocalidad';
    protected $table = 'g_interoperabilidad_can.notificaciones_resulatos_envio';
    protected $primaryKey = 'id_nre';

    protected $fillable = [
        'id_solicitud',
        'codigo_formato',
        'punto_destino',
        'fecha_recepcion_intercom',
        'fecha_recepcion_destino',
        'estado_documento',
        'supero_cantidad_intentos',
        'tipo_documento'
    ];

    const CREATED_AT = 'fecha_registro';
    const UPDATED_AT = 'fecha_actualizacion';

    public function erroresNotificacionesResultadoEnvio() {
        return $this->hasMany(ErroresNotificacionesResultadoEnvio::class, 'id_nre', 'id_nre');
    }

}