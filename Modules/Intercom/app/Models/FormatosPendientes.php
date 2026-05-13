<?php

namespace Modules\Intercom\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormatosPendientes extends Model
{

    use HasFactory;

    protected $connection = 'agrocalidad';
    protected $table = 'g_interoperabilidad_can.formatos_pendiente';
    protected $primaryKey = 'id_fpendiente';

    protected $fillable = [
        'funcion',
        'tipo_codigo',
        'tipo_formato',
        'numero_pagina',
        'total_pagina',
        'total_registros',
        'tamano_pagina'
    ];

    const CREATED_AT = 'fecha_registro';
    const UPDATED_AT = 'fecha_actualizacion';

    public function listadoFormatosPendientes() {
        return $this->hasMany(ListadoFormatosPendientes::class, 'id_fpendiente', 'id_fpendiente');
    }
}
