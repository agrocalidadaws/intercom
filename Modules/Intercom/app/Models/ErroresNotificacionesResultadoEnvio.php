<?php

namespace Modules\Intercom\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErroresNotificacionesResultadoEnvio extends Model {

    use HasFactory;

    protected $connection = 'agrocalidad';
    protected $table = 'g_interoperabilidad_can.errores_notificaciones_resulatos_envio';
    protected $primaryKey = 'id_enre';

    protected $fillable = [
        'id_nre',
        'codigo_error',
        'detalle_error',
    ];

    public $timestamps = false;

    public function notificacionesResultadosEnvio() {
        return $this->belongsTo(NotificacionesResultadosEnvio::class, 'id_nre', 'id_nre');
    }

}