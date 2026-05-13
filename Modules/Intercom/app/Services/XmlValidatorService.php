<?php

namespace Modules\Intercom\Services;

class XmlValidatorService
{

    public $estructuraEsperadaMBA005 = [
        'Function' => ['obligatorio' => true],
        'TypeCode' => ['obligatorio' => true],
        'SPSCertificates' => [
            'obligatorio' => true,
            'estructura' => [
                'SPSCertificate' => [
                    'obligatorio' => true,
                    'repetible' => true,
                    'estructura' => [
                        'FunctionalReferenceID' => ['obligatorio' => true],
                        'FormatID' => ['obligatorio' => true],
                        'ID' => ['obligatorio' => true],
                        'Submitter' => [
                            'obligatorio' => true,
                            'estructura' => [
                                'IdentificationIssuingCountryCode' => ['obligatorio' => true]
                            ]
                        ],
                        'IssueDateTime' => ['obligatorio' => true, 'tipo' => 'fecha'],
                        'RequestStatus' => ['obligatorio' => true],
                        'ReceivingDateTime' => ['obligatorio' => true, 'tipo' => 'fecha']
                    ]
                ]
            ]
        ]
    ];

    public $estructuraEsperadaMBA011 = [
        'Function' => ['obligatorio' => true],
        'TypeCode' => ['obligatorio' => true],
        'SPSCertificate' => [
            'obligatorio' => true,
            'estructura' => [
                'StatusCode' => ['obligatorio' => true],
                'Base64File' => ['obligatorio' => true]
            ]
        ]
    ];

    public $estructuraEsperadaMEX501 = [
        'fechaEmision' => ['obligatorio' => false, 'tipo' => 'fecha'],
        'codigoFormato' => ['obligatorio' => true],
        'puntoOrigen' => ['obligatorio' => true],
        'formato' => ['obligatorio' => true],
    ];

    public $estructuraEsperadaMEX502 = [
        'idSolicitud' => ['requerido' => true],
        'formatos' => [
            'requerido' => true,
            'subestructura' => [
                'detalleFormato' => [
                    'requerido' => true,
                    'repetido' => true,
                    'subestructura' => [
                        'codigoFormato' => ['requerido' => true],
                        'estadoDocumento' => ['requerido' => true],
                        'puntoDestino' => ['requerido' => true],
                        'fechaRecepcionIntercom' => ['requerido' => true, 'tipo' => 'fecha'],
                        'fechaRecepcionDestino' => ['requerido' => true, 'tipo' => 'fecha'],
                        'superaCantidadIntentos' => ['requerido' => true, 'tipo' => 'booleano'],
                        'erroresDocumento' => [
                            'requerido' => false,
                            'subestructura' => [
                                'errorDocumento' => [
                                    'requerido' => true,
                                    'repetido' => true,
                                    'subestructura' => [
                                        'idError' => ['requerido' => true],
                                        'detalleError' => ['requerido' => true],
                                    ],
                                    'soloEstasEtiquetas' => ['idError', 'detalleError'],
                                ]
                            ]
                        ],
                    ],
                ]
            ]
        ]
    ];

    public $estructuraEsperadaMEX503 = [
        'codigoFormato' => ['obligatorio' => true],
        'puntoDestino' => ['obligatorio' => true]
    ];

    public function validarEstructuraMBA005(\SimpleXMLElement $xml, string $ruta = ''): array
    {
        $errores = [];

        foreach ($this->estructuraEsperadaMBA005 as $etiqueta => $config) {
            $esRepetido = $config['repetible'] ?? false;
            $obligatorio = $config['obligatorio'] ?? false;
            $subestructura = $config['estructura'] ?? null;
            $etiquetasEncontradas = $xml->{$etiqueta};
            $tipo = $config['tipo'] ?? null;

            if (!$etiquetasEncontradas && $obligatorio) {
                $errores[] = "Falta la etiqueta obligatoria $etiqueta en $ruta";
                continue;
            }

            $instancias = $esRepetido ? $etiquetasEncontradas : [$etiquetasEncontradas];

            foreach ($instancias as $index => $nodo) {
                if ($nodo instanceof \SimpleXMLElement) {
                    $subRuta = $ruta . "/$etiqueta" . ($esRepetido ? "[$index]" : '');

                    $valor = trim((string)$nodo);

                    // Verificar si está vacío (sin texto) y es obligatorio
                    if ($obligatorio && $valor === '' && !$subestructura) {
                        $errores[] = "La etiqueta $etiqueta en $subRuta está vacía y es obligatoria";
                    }

                    if ($tipo && $valor !== '') {
                        switch ($tipo) {
                            case 'fecha':

                                if (!$this->esFechaValida($valor)) {
                                    $errores[] = "La etiqueta $etiqueta en $subRuta no contiene una fecha válida";
                                }
                                break;

                            case 'booleano':
                                if (!in_array(strtolower($valor), ['true', 'false', '1', '0'], true)) {
                                    $errores[] = "La etiqueta $etiqueta en $subRuta debe ser booleano (true/false/1/0)";
                                }
                                break;
                        }
                    }

                    if ($subestructura) {
                        $errores = array_merge(
                            $errores,
                            $this->validarEstructura($nodo, $subestructura, $subRuta)
                        );
                    }
                }
            }
        }
        return $errores;
    }

    public function validarEstructuraMBA011(\SimpleXMLElement $xml, string $ruta = ''): array
    {
        $errores = [];

        foreach ($this->estructuraEsperadaMBA011 as $etiqueta => $config) {
            $esRepetido = $config['repetible'] ?? false;
            $obligatorio = $config['obligatorio'] ?? false;
            $subestructura = $config['estructura'] ?? null;
            $etiquetasEncontradas = $xml->{$etiqueta};
            $tipo = $config['tipo'] ?? null;

            if (!$etiquetasEncontradas && $obligatorio) {
                $errores[] = "Falta la etiqueta obligatoria $etiqueta en $ruta";
                continue;
            }

            $instancias = $esRepetido ? $etiquetasEncontradas : [$etiquetasEncontradas];

            foreach ($instancias as $index => $nodo) {
                if ($nodo instanceof \SimpleXMLElement) {
                    $subRuta = $ruta . "/$etiqueta" . ($esRepetido ? "[$index]" : '');

                    $valor = trim((string)$nodo);

                    // Verificar si está vacío (sin texto) y es obligatorio
                    if ($obligatorio && $valor === '' && !$subestructura) {
                        $errores[] = "La etiqueta $etiqueta en $subRuta está vacía y es obligatoria";
                    }

                    if ($tipo && $valor !== '') {
                        switch ($tipo) {
                            case 'fecha':

                                if (!$this->esFechaValida($valor)) {
                                    $errores[] = "La etiqueta $etiqueta en $subRuta no contiene una fecha válida";
                                }
                                break;

                            case 'booleano':
                                if (!in_array(strtolower($valor), ['true', 'false', '1', '0'], true)) {
                                    $errores[] = "La etiqueta $etiqueta en $subRuta debe ser booleano (true/false/1/0)";
                                }
                                break;
                        }
                    }

                    if ($subestructura) {
                        $errores = array_merge(
                            $errores,
                            $this->validarEstructura($nodo, $subestructura, $subRuta)
                        );
                    }
                }
            }
        }
        return $errores;
    }

    public function validarEstructuraMEX501(\SimpleXMLElement $xml, string $ruta = ''): array
    {
        $errores = [];

        $etiquetas = [];

        foreach ($xml->children() as $child) {
            $nombre = $child->getName();
            if (in_array($nombre, $etiquetas)) {
                $errores[] = "La etiqueta $nombre se repite";
            } else {
                $etiquetas[] = $nombre;
            }
        }

        $etiquetasXml = array_map(
            fn($nodo) => $nodo->getName(),
            iterator_to_array($xml->children())
        );

        $etiquetasEsperadas = array_keys($this->estructuraEsperadaMEX501);

        foreach ($etiquetasXml as $nombreEtiquetaXml) {
            if (!in_array($nombreEtiquetaXml, $etiquetasEsperadas)) {
                $errores[] = "Etiqueta $nombreEtiquetaXml no está permitida";
            }
        }

        foreach ($this->estructuraEsperadaMEX501 as $etiqueta => $config) {
            $obligatorio = $config['obligatorio'] ?? false;
            $etiquetasEncontradas = $xml->{$etiqueta};
            $tipo = $config['tipo'] ?? null;

            if (!$etiquetasEncontradas && $obligatorio) {
                $errores[] = "Falta la etiqueta obligatoria $etiqueta";
                continue;
            }

            $valor = trim((string)$etiquetasEncontradas);
            if ($obligatorio && $valor === '') {
                $errores[] = "La etiqueta $etiqueta está vacía y es obligatoria";
            }

            if ($tipo == 'fecha' && $obligatorio) {
                if (!$this->esFechaValida($valor)) {
                    $errores[] = "La etiqueta $etiqueta no contiene una fecha válida";
                }
            }
        }

        return $errores;
    }

    public function validarEstructuraMEX502(\SimpleXMLElement $xml, string $ruta = ''): array
    {
        $errores = [];
        $codigos = [];
        $indice = 1;

        foreach ($xml->formatos->detalleFormato as $detalleFormato) {
            $codigo = trim((string)$detalleFormato->codigoFormato);
            if ($codigo === '') continue;

            if (in_array($codigo, $codigos)) {
                $errores[] = "El valor $codigo del etiqueta codigoFormato está duplicado en {$ruta}[{$indice}]";
            } else {
                $codigos[] = $codigo;
            }

            $indice++;
        }

        if ($xml !== null && $xml->count() > 0) {
            $etiquetasXml = array_map(
                fn($nodo) => $nodo->getName(),
                iterator_to_array($xml->children())
            );
        } else {
            $etiquetasXml = [];
        }

        $etiquetasEsperadas = array_keys($this->estructuraEsperadaMEX502);

        foreach ($etiquetasXml as $nombreEtiquetaXml) {
            if (!in_array($nombreEtiquetaXml, $etiquetasEsperadas)) {
                $errores[] = "Etiqueta $nombreEtiquetaXml en $ruta no está permitida";
            }
        }

        foreach ($this->estructuraEsperadaMEX502 as $etiqueta => $config) {
            $nodos = $xml->$etiqueta;

            $existe = count($nodos) > 0;

            if (!$existe) {
                if (($config['requerido'] ?? false)) {
                    $errores[] = "Falta la etiqueta <$etiqueta> en {$ruta}";
                }
                continue;
            }

            if (!($config['repetido'] ?? false) && count($nodos) > 1) {
                $errores[] = "La etiqueta <$etiqueta> no debe repetirse en {$ruta}";
            }

            $index = 1;
            foreach ($nodos as $nodo) {
                $valor = trim((string)$nodo);

                if (($config['requerido'] ?? false) && $valor === '' && empty($config['subestructura'])) {
                    $errores[] = "La etiqueta <$etiqueta> está vacía en {$ruta}{$etiqueta}[{$index}]";
                }

                if (!empty($config['tipo']) && $valor !== '') {
                    switch ($config['tipo']) {
                        case 'fecha':
                            if (!$this->esFechaValida($valor)) {
                                $errores[] = "La etiqueta <$etiqueta> debe ser fecha válida en {$ruta}{$etiqueta}[{$index}] (valor: $valor)";
                            }
                            break;
                        case 'booleano':
                            if (!in_array(strtolower($valor), ['true', 'false', '1', '0'], true)) {
                                $errores[] = "La etiqueta <$etiqueta> debe ser booleana en {$ruta}{$etiqueta}[{$index}] (valor: $valor)";
                            }
                            break;
                    }
                }

                if (!empty($config['subestructura'])) {
                    $errores = array_merge(
                        $errores,
                        $this->validarEstructura($nodo, $config['subestructura'], "{$ruta}{$etiqueta}[{$index}]/")
                    );
                }

                if (!empty($config['soloEstasEtiquetas'])) {
                    $etiquetasXml = [];
                    if ($nodo->children()) {
                        $etiquetasXml = array_map(
                            fn($child) => $child->getName(),
                            iterator_to_array($nodo->children())
                        );
                    }
                    foreach ($etiquetasXml as $etq) {
                        if (!in_array($etq, $config['soloEstasEtiquetas'])) {
                            $errores[] = "Etiqueta <$etq> no permitida dentro de <$etiqueta> en {$ruta}{$etiqueta}[{$index}]";
                        }
                    }
                }

                $index++;
            }
        }

        return $errores;
    }

    public function validarEstructura(\SimpleXMLElement $xml, array $estructura, string $ruta = ''): array
    {
        $errores = [];

        if ($xml !== null && $xml->count() > 0) {
            $etiquetasXml = array_map(
                fn($nodo) => $nodo->getName(),
                iterator_to_array($xml->children())
            );
        } else {
            $etiquetasXml = [];
        }

        $etiquetasEsperadas = array_keys($estructura);

        foreach ($etiquetasXml as $nombreEtiquetaXml) {
            if (!in_array($nombreEtiquetaXml, $etiquetasEsperadas)) {
                $errores[] = "Etiqueta $nombreEtiquetaXml en $ruta no está permitida";
            }
        }

        foreach ($estructura as $etiqueta => $config) {
            $nodos = $xml->$etiqueta;

            $existe = count($nodos) > 0;

            if (!$existe) {
                if (($config['requerido'] ?? false)) {
                    $errores[] = "Falta la etiqueta <$etiqueta> en {$ruta}";
                }
                continue;
            }

            if (!($config['repetido'] ?? false) && count($nodos) > 1) {
                $errores[] = "La etiqueta <$etiqueta> no debe repetirse en {$ruta}";
            }

            $index = 1;
            foreach ($nodos as $nodo) {
                $valor = trim((string)$nodo);

                if (($config['requerido'] ?? false) && $valor === '' && empty($config['subestructura'])) {
                    $errores[] = "La etiqueta <$etiqueta> está vacía en {$ruta}{$etiqueta}[{$index}]";
                }

                if (!empty($config['tipo']) && $valor !== '') {
                    switch ($config['tipo']) {
                        case 'fecha':
                            if (!$this->esFechaValida($valor)) {
                                $errores[] = "La etiqueta <$etiqueta> debe ser fecha válida en {$ruta}{$etiqueta}[{$index}] (valor: $valor)";
                            }
                            break;
                        case 'booleano':
                            if (!in_array(strtolower($valor), ['true', 'false', '1', '0'], true)) {
                                $errores[] = "La etiqueta <$etiqueta> debe ser booleana en {$ruta}{$etiqueta}[{$index}] (valor: $valor)";
                            }
                            break;
                    }
                }

                if (!empty($config['subestructura'])) {
                    $errores = array_merge(
                        $errores,
                        $this->validarEstructura($nodo, $config['subestructura'], "{$ruta}{$etiqueta}[{$index}]/")
                    );
                }

                if (!empty($config['soloEstasEtiquetas'])) {
                    $etiquetasXml = [];
                    if ($nodo->children()) {
                        $etiquetasXml = array_map(
                            fn($child) => $child->getName(),
                            iterator_to_array($nodo->children())
                        );
                    }
                    foreach ($etiquetasXml as $etq) {
                        if (!in_array($etq, $config['soloEstasEtiquetas'])) {
                            $errores[] = "Etiqueta <$etq> no permitida dentro de <$etiqueta> en {$ruta}{$etiqueta}[{$index}]";
                        }
                    }
                }

                $index++;
            }
        }

        return $errores;
    }

    public function validarEstructuraMEX503(\SimpleXMLElement $xml, string $ruta = ''): array
    {
        $errores = [];
        $etiquetas = [];

        foreach ($xml->children() as $child) {
            $nombre = $child->getName();
            if (in_array($nombre, $etiquetas)) {
                $errores[] = "La etiqueta $nombre se repite";
            } else {
                $etiquetas[] = $nombre;
            }
        }

        $etiquetasXml = array_map(
            fn($nodo) => $nodo->getName(),
            iterator_to_array($xml->children())
        );

        $etiquetasEsperadas = array_keys($this->estructuraEsperadaMEX503);

        foreach ($etiquetasXml as $nombreEtiquetaXml) {
            if (!in_array($nombreEtiquetaXml, $etiquetasEsperadas)) {
                $errores[] = "Etiqueta $nombreEtiquetaXml no está permitida";
            }
        }

        foreach ($this->estructuraEsperadaMEX503 as $etiqueta => $config) {
            $obligatorio = $config['obligatorio'] ?? false;
            $etiquetasEncontradas = $xml->{$etiqueta};

            $valor = trim((string)$etiquetasEncontradas);
            if ($obligatorio && $valor === '') {
                $errores[] = "La etiqueta $etiqueta está vacía y es obligatoria";
            }

            if (!$etiquetasEncontradas && $obligatorio) {
                $errores[] = "Falta la etiqueta obligatoria $etiqueta";
                continue;
            }
        }

        return $errores;
    }

    private function esFechaValida(string $valor): bool
    {
        // Formatos permitidos
        $formatos = ['Y-m-d', 'Y-m-d H:i:s', 'd/m/Y', 'Y-m-d\TH:i', 'Y-m-d\TH:i:s', 'Y-m-d\TH:i:sZ', 'Y-m-d\TH:i:sP', 'Y-m-d\TH:i:s.uP'];

        foreach ($formatos as $formato) {
            $dt = \DateTime::createFromFormat($formato, $valor);
            if ($dt && $dt->format($formato) === $valor) {
                return true;
            }
        }

        try {
            $dt = new \DateTime($valor);
            return true;
        } catch (\Exception) {
            return false;
        }

    }
}
