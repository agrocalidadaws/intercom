<?php

namespace Modules\Intercom\Models\cfe;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductoDescripcionPaqueteCFE extends Model
{

    use HasFactory;

    protected $connection = 'agrocalidad';
    protected $table = 'g_certificado_cfe_can.productos_descripcion_paquetes_cfe';
    protected $primaryKey = 'id_pdp';

    protected $fillable = [
        'id_procer',
        'codigo_nivel_embalaje',
        'codigo_tipo_paquete',
        'numero_paquetes'
    ];

    public $timestamps = false;

    public function productosCertificadosCFE() {
        return $this->hasMany(ProductosCertificadosCFE::class, 'id_procer', 'id_procer');
    }
}