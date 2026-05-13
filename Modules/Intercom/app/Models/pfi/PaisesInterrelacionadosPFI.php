<?php

namespace Modules\Intercom\Models\pfi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaisesInterrelacionadosPFI extends Model
{

    use HasFactory;

    protected $connection = 'agrocalidad';
    protected $table = 'g_permiso_pfi_can.paises_interrelacionados_pfi';
    protected $primaryKey = 'id_pain';

    protected $fillable = [
        'id_pfi',
        'id_pais',
        'nombre',
        'tipo',
    ];

    public $timestamps = false;

    public function PermisoPFI() {
        return $this->belongsTo(PermisoPFI::class, 'id_pfi', 'id_pfi');
    }
    
}
