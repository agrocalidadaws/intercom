<?php

namespace Modules\Intercom\Models\pfi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductosInformacionAdicionalPFI extends Model
{

    use HasFactory;

    protected $connection = 'agrocalidad';
    protected $table = 'g_permiso_pfi_can.productos_informacion_adicional_pfi';
    protected $primaryKey = 'id_pinfa';

    protected $fillable = [
        'id_producto_permiso',
        'pinfa_descripcion',
        'pinfa_contenido'
    ];

    public $timestamps = false;

    public function productosPersmisoPFI() {
        return $this->belongsTo(ProductosPersmisoPFI::class, 'id_producto_permiso', 'id_producto_permiso');
    }
}
