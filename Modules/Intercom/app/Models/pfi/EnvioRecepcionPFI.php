<?php

namespace Modules\Intercom\Models\pfi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnvioRecepcionPFI extends Model
{

    use HasFactory;

    protected $connection = 'agrocalidad';
    protected $table = 'g_permiso_pfi_can.envio_recepcion_pfi';
    protected $primaryKey = 'id_enre';

    protected $fillable = [
        'id_pfi',
        'nombre_consigna',
        'direccion_consignador_uno',
        'direccion_consignador_dos',
        'direccion_consignador_tres',
        'direccion_consignador_cuatro',
        'direccion_consignador_cinco',
        'tipo',
    ];

    public $timestamps = false;

    public function PermisoPFI() {
        return $this->belongsTo(PermisoPFI::class, 'id_pfi', 'id_pfi');
    }
}
