<?php

namespace Modules\Intercom\Models\cfe;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CertificadoCFE extends Model
{

    use HasFactory;

    protected $connection = 'agrocalidad';
    protected $table = 'g_certificado_cfe_can.certificado_cfe';
    protected $primaryKey = 'id_cfe';
    protected $fillable = [
        'numero_certificado',
        'nombre_certificado',
        'estado_cambio',
        'fecha_emision',
        'proteccion_fitosanitaria',
        'documento_fecha_emision',
        'referencia_original_emision',
        'numero_fitosanitario_original',
        'archivo_adjunto_path',
        'descripcion_documento',
        'lugar_emision',
        'funcionario_autorizado',
        'certificacion_estandar',
        'medio_transporte',
        'modo_transporte',
        'nombre_trasporte',
        'numero_sello',
        'punto_origen',
        'envio_recepcion_documento'
    ];

    const CREATED_AT = 'fecha_registro'; 
    const UPDATED_AT = 'fecha_actualizacion';

    public function informacionAdicionalCFE() {
        return $this->hasMany(InformacionAdicionalCFE::class, 'id_cfe', 'id_cfe');
    }

    public function envioRecepcionCFE() {
        return $this->hasMany(EnvioRecepcionCFE::class, 'id_cfe', 'id_cfe');
    }

    public function paisesInterrelacionadosCFE() {
        return $this->hasMany(PaisesInterrelacionadosCFE::class, 'id_cfe', 'id_cfe');
    }

    public function productosCertificadosCFE() {
        return $this->hasMany(ProductosCertificadosCFE::class, 'id_cfe', 'id_cfe');
    }
}
