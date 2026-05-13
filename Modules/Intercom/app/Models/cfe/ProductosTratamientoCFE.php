<?php

namespace Modules\Intercom\Models\cfe;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductosTratamientoCFE extends Model
{
    use HasFactory;

    protected $connection = 'agrocalidad';
    protected $table = 'g_certificado_cfe_can.productos_tratamiento_cfe';
    protected $primaryKey = 'id_ptr';

    protected $fillable = [
        'id_procer',
        'tipo_codigo_tra',
        'fecha_inicio_tra',
        'fecha_final_tra',
        'duracion_tra'
    ];

    public $timestamps = false;

    public function productosCertificadosCFE() {
        return $this->belongsTo(ProductosCertificadosCFE::class, 'id_procer', 'id_procer');
    }

    public function productosTiposTratamientosCFE() {
        return $this->hasMany(ProductosTiposTratamientosCFE::class, 'id_ptr', 'id_ptr');
    }

    
}