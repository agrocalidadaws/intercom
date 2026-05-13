<?php

namespace Modules\Intercom\Models\pfi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductosClasesPFI extends Model
{

    use HasFactory;

    protected $connection = 'agrocalidad';
    protected $table = 'g_permiso_pfi_can.productos_clase_pfi';
    protected $primaryKey = 'id_pcl';

    protected $fillable = [
        'id_producto_permiso',
        'nombre_sistema',
        'clase_codigo',
    ];

    public $timestamps = false;

    public function productosPersmisoPFI() {
        return $this->belongsTo(ProductosPersmisoPFI::class, 'id_producto_permiso', 'id_producto_permiso');
    }
    
}
