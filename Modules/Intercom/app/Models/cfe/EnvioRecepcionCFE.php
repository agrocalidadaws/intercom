<?php

namespace Modules\Intercom\Models\cfe;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnvioRecepcionCFE extends Model
{

    use HasFactory;

    protected $connection = 'agrocalidad';
    protected $table = 'g_certificado_cfe_can.envio_recepcion_cfe';
    protected $primaryKey = 'id_enre';

    protected $fillable = [
        'id_cfe',
        'nombre_consigna',
        'direccion_consignador_uno',
        'direccion_consignador_dos',
        'direccion_consignador_tres',
        'direccion_consignador_cuatro',
        'direccion_consignador_cinco',
        'tipo',
    ];

    public $timestamps = false;

    public function certificadoCFE() {
        return $this->belongsTo(CertificadoCFE::class, 'id_cfe', 'id_cfe');
    }
}
