<?php

namespace Modules\Intercom\Models\cfe;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductosInformacionAdicionalCFE extends Model
{

    use HasFactory;

    protected $connection = 'agrocalidad';
    protected $table = 'g_certificado_cfe_can.productos_informacion_adicional_cfe';
    protected $primaryKey = 'id_pinfa';

    protected $fillable = [
        'id_procer',
        'pinfa_descripcion',
        'pinfa_contenido'
    ];

    public $timestamps = false;

    public function productosCertificadosCFE() {
        return $this->belongsTo(ProductosCertificadosCFE::class, 'id_procer', 'id_procer');
    }
}
