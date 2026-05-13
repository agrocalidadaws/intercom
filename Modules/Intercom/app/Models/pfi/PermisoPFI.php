<?php

namespace Modules\Intercom\Models\pfi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermisoPFI extends Model
{

    use HasFactory;

    protected $connection = 'agrocalidad';
    protected $table = 'g_permiso_pfi_can.permiso_pfi';
    protected $primaryKey = 'id_pfi';
    protected $fillable = [
        'numero_permiso',
        'estado_cambio',
        'fecha_emision',
        'dias_vigencia',
        'proteccion_fitosanitaria',
        'nombre_funcionario',
        'documento_fecha_emision',
        'referencia_original_emision',
        'numero_fitosanitario_original',
        'archivo_adjunto_path',
        'descripcion_documento',
        'lugar_emision',
        'medio_transporte',
        'modo_transporte',
        'nombre_trasporte',
        'numero_sello',
        'punto_origen',
        'envio_recepcion_documento'
    ];

    const CREATED_AT = 'fecha_registro';
    const UPDATED_AT = 'fecha_actualizacion';

    public function informacionAdicionalPFI()
    {
        return $this->hasMany(InformacionAdicionalPFI::class, 'id_pfi', 'id_pfi');
    }

    public function envioRecepcionPFI()
    {
        return $this->hasMany(EnvioRecepcionPFI::class, 'id_pfi', 'id_pfi');
    }

    public function paisesInterrelacionadosPFI()
    {
        return $this->hasMany(PaisesInterrelacionadosPFI::class, 'id_pfi', 'id_pfi');
    }

    public function productosPersmisoPFI()
    {
        return $this->hasMany(ProductosPersmisoPFI::class, 'id_pfi', 'id_pfi');
    }
}
