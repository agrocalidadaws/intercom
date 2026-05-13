<?php

namespace Modules\Intercom\Models\cfe;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaisesInterrelacionadosCFE extends Model
{

    use HasFactory;

    protected $connection = 'agrocalidad';
    protected $table = 'g_certificado_cfe_can.paises_interrelacionados_cfe';
    protected $primaryKey = 'id_pain';

    protected $fillable = [
        'id_cfe',
        'id_pais',
        'nombre',
        'tipo',
    ];

    public $timestamps = false;

    public function certificadoCFE() {
        return $this->belongsTo(CertificadoCFE::class, 'id_cfe', 'id_cfe');
    }
    
}
