<?php

namespace Modules\Intercom\Models\cfe;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductosCertificadosCFE extends Model
{

    use HasFactory;

    protected $connection = 'agrocalidad';
    protected $table = 'g_certificado_cfe_can.productos_certificados_cfe';
    protected $primaryKey = 'id_procer';

    protected $fillable = [
        'id_cfe',
        'descripcion',
        'nombre_comun',
        'nombre_cientifico',
        'producto_ippc',
        'peso_neto',
        'peso_bruto',
        'volumen_neto',
        'volumen_bruto',
        'pais_orige_id',
        'nombre_zona_dentro_po'
    ];

    public $timestamps = false;

    public function certificadoCFE() {
        return $this->belongsTo(CertificadoCFE::class, 'id_cfe', 'id_cfe');
    }

    public function productosInformacionAdicionalCFE() {
        return $this->hasMany(ProductosInformacionAdicionalCFE::class, 'id_procer', 'id_procer');
    }

    public function productosClasesCFE() {
        return $this->hasMany(ProductosClasesCFE::class, 'id_procer', 'id_procer');
    }

    public function productoDescripcionPaqueteCFE() {
        return $this->hasMany(ProductoDescripcionPaqueteCFE::class, 'id_procer', 'id_procer');
    }

    public function productosTratamientoCFE() {
        return $this->hasMany(ProductosTratamientoCFE::class, 'id_procer', 'id_procer');
    }
}
