<?php

namespace Modules\Intercom\Models\cfe;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductosTiposTratamientosCFE extends Model
{
    use HasFactory;

    protected $connection = 'agrocalidad';
    protected $table = 'g_certificado_cfe_can.productos_tipo_tratamiento';
    protected $primaryKey = 'id_ttr';

    protected $fillable = [
        'id_ptr',
        'descripcion_tra_uno',
        'descripcion_tra_dos'
    ];

    public $timestamps = false;

    public function productosTratamientoCFE() {
        return $this->belongsTo(ProductosTratamientoCFE::class, 'id_ptr', 'id_ptr');
    }

}