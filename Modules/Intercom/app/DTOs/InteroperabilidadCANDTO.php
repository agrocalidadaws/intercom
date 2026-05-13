<?php

namespace Modules\Intercom\DTOs;

class InteroperabilidadCANDTO extends BaseDTO
{

    public function __construct(
        public int $idCertificadoPermiso,
        public string $codigoFitosanitarioO,
        public string $codigoFitosanitarioC,
        public string $tipoDocumento,
        public string $metodo,
        public string $estadoDocumento,
        public string $respuestaIntercom,
        public string $parthArchivo,
        public string $ejecutadoPor
    ) {}

    public function toArray(): array
    {
        return [
            'idCertificadoPermiso' => $this->idCertificadoPermiso,
            'codigoFitosanitarioO' => $this->codigoFitosanitarioO,
            'codigoFitosanitarioC' => $this->codigoFitosanitarioC,
            'tipoDocumento' => $this->tipoDocumento,
            'metodo' => $this->metodo,
            'estadoDocumento' => $this->estadoDocumento,
            'respuestaIntercom' => $this->respuestaIntercom,
            'parthArchivo' => $this->parthArchivo,
            'ejecutadoPor' => $this->ejecutadoPor
        ];
    }
}
