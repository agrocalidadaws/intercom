<?php

namespace Modules\Intercom\Models\cfe;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InformacionAdicionalCFE extends Model
{

    use HasFactory;

    protected $connection = 'agrocalidad';
    protected $table = 'g_certificado_cfe_can.informacion_adicional_cfe';
    protected $primaryKey = 'id_ifa';

    protected $fillable = [
        'id_cfe',
        'descripcion',
        'contenido',
    ];

    public $timestamps = false;

    public function certificadoCFE() {
        return $this->belongsTo(CertificadoCFE::class, 'id_cfe', 'id_cfe');
    }
}
