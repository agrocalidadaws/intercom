<?php

namespace Modules\Intercom\Models\pfi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductoDescripcionPaquetePFI extends Model
{

    use HasFactory;

    protected $connection = 'agrocalidad';
    protected $table = 'g_permiso_pfi_can.productos_descripcion_paquetes_pfi';
    protected $primaryKey = 'id_pdp';

    protected $fillable = [
        'id_producto_permiso',
        'codigo_nivel_embalaje',
        'cofigo_tipo_paquete',
        'numero_paquetes'
    ];

    public $timestamps = false;

    public function productosPersmisoPFI() {
        return $this->belongsTo(ProductosPersmisoPFI::class, 'id_producto_permiso', 'id_producto_permiso');
    }
}