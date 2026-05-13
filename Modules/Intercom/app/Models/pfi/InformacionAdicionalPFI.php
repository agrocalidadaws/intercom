<?php

namespace Modules\Intercom\Models\pfi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InformacionAdicionalPFI extends Model
{

    use HasFactory;

    protected $connection = 'agrocalidad';
    protected $table = 'g_permiso_pfi_can.informacion_adicional_pfi';
    protected $primaryKey = 'id_ifa';

    protected $fillable = [
        'id_pfi',
        'descripcion',
        'contenido',
    ];

    public $timestamps = false;

    public function permisoPFI() {
        return $this->belongsTo(PermisoPFI::class, 'id_pfi', 'id_pfi');
    }
}
