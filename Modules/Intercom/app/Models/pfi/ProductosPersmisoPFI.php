<?php

namespace Modules\Intercom\Models\pfi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductosPersmisoPFI extends Model
{

    use HasFactory;

    protected $connection = 'agrocalidad';
    protected $table = 'g_permiso_pfi_can.productos_permiso_pfi';
    protected $primaryKey = 'id_producto_permiso';

    protected $fillable = [
        'id_pfi',
        'descripcion',
        'nombre_comun',
        'nombre_cientifico',
        'producto_ippc',
        'peso_neto',
        'peso_bruto',
        'volumen_neto',
        'volumen_bruto',
        'pais_origen_id',
        'nombre_pais',
        'nombre_zona_dentro_po'
    ];

    public $timestamps = false;

    public function permisoPFI()
    {
        return $this->belongsTo(PermisoPFI::class, 'id_pfi', 'id_pfi');
    }

    public function productosInformacionAdicionalPFI()
    {
        return $this->hasMany(ProductosInformacionAdicionalPFI::class, 'id_producto_permiso', 'id_producto_permiso');
    }

    public function productosClasesPFI()
    {
        return $this->hasMany(ProductosClasesPFI::class, 'id_producto_permiso', 'id_producto_permiso');
    }

    public function productoDescripcionPaquetePFI()
    {
        return $this->hasMany(ProductoDescripcionPaquetePFI::class, 'id_producto_permiso', 'id_producto_permiso');
    }

}
