<?php

namespace Modules\Intercom\Models\cfe;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductosClasesCFE extends Model
{

    use HasFactory;

    protected $connection = 'agrocalidad';
    protected $table = 'g_certificado_cfe_can.productos_clase_cfe';
    protected $primaryKey = 'id_pcl';

    protected $fillable = [
        'id_procer',
        'nombre_sistema',
        'clase_codigo',
    ];

    public $timestamps = false;

    public function productosCertificadosCFE() {
        return $this->belongsTo(ProductosCertificadosCFE::class, 'id_procer', 'id_procer');
    }
    
}
