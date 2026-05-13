<?php

namespace Modules\Intercom\Services;

use Modules\Intercom\Domain\Xml\IntercomConstants;



class IntercomApiServices extends BaseIntercomService
{

    public function sendMbaCFE0002($xmlData)
    {
        $MBA002_URL = IntercomConstants::MBA_CFE002_CFE;

        try {
            $token = $this->getAccessToken();

            $response = $this->httpClient
                ->withToken($token['access_token'])
                ->withOptions(['verify' => false])
                ->withBody($xmlData, 'application/xml')
                ->post($MBA002_URL);

            return [
                'status' => $response->getStatusCode(),
                'data'   => $response->body(),
            ];
        } catch (\Exception $e) {
            $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 500;

            return [
                'status' => $statusCode,
                'error'  => $e->getMessage(),
            ];
        }
    }

    public function sendMbaPFI0002($xmlData)
    {
        $MBA002_URL = IntercomConstants::MBA_CFE002_PFI;

        try {
            $token = $this->getAccessToken();

            $response = $this->httpClient
                ->withToken($token['access_token'])
                ->withOptions(['verify' => false])
                ->withBody($xmlData, 'application/xml')
                ->post($MBA002_URL);

            return [
                'status' => $response->getStatusCode(),
                'data'   => $response->body(),
            ];
        } catch (\Exception $e) {
            $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 500;

            return [
                'status' => $statusCode,
                'error'  => $e->getMessage(),
            ];
        }
    }

    public function getListadoCFERecibidosPDMba005($parametrosBusquedaCFE)
    {
        try {
            $MBA005_URL = IntercomConstants::MBA_CFE005_CFE;

            $token = $this->getAccessToken();
            $signoCoonsulta = '?';

            if ($parametrosBusquedaCFE->puntoOrigen != '') {
                $MBA005_URL .= '?';
                $MBA005_URL .= 'puntoOrigen=' . $parametrosBusquedaCFE->puntoOrigen;
                $signoCoonsulta = '&';
            }

            if ($parametrosBusquedaCFE->fechaEnvioDesde != '' && $parametrosBusquedaCFE->fechaEnvioHasta != '') {
                $MBA005_URL .= $signoCoonsulta . 'fechaEnvioDesde=' . $parametrosBusquedaCFE->fechaEnvioDesde . '&' . $parametrosBusquedaCFE->fechaEnvioHasta;
                $signoCoonsulta = '&';
            }

            if ((int)$parametrosBusquedaCFE->registrosPagina != 0) {
                $MBA005_URL .= $signoCoonsulta . 'registrosPagina=' . $parametrosBusquedaCFE->registrosPagina;
                $signoCoonsulta = '&';
            }

            if ((int)$parametrosBusquedaCFE->numeroPagina != 0) {
                $MBA005_URL .= $signoCoonsulta . 'numeroPagina=' . $parametrosBusquedaCFE->numeroPagina;
            }

            $response = $this->httpClient
                ->withToken($token['access_token'])
                ->withOptions(['verify' => false])
                ->get($MBA005_URL);
            return [
                'status' => $response->getStatusCode(),
                'data'   => $response->body(),
            ];
        } catch (\Exception $e) {
            $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 500;

            return [
                'status' => $statusCode,
                'error'  => $e->getMessage(),
            ];
        }
    }

    public function getListadoPFIRecibidosPDMba005($parametrosBusquedaCFE)
    {
        try {
            $MBA005_URL = IntercomConstants::MBA_CFE005_PFI;
            $token = $this->getAccessToken();
            $signoCoonsulta = '?';

            if ($parametrosBusquedaCFE->puntoOrigen != '') {
                $MBA005_URL .= '?';
                $MBA005_URL .= 'puntoOrigen=' . $parametrosBusquedaCFE->puntoOrigen;
                $signoCoonsulta = '&';
            }

            if ($parametrosBusquedaCFE->fechaEnvioDesde != '' && $parametrosBusquedaCFE->fechaEnvioHasta != '') {
                $MBA005_URL .= $signoCoonsulta . 'fechaEnvioDesde=' . $parametrosBusquedaCFE->fechaEnvioDesde . '&' . $parametrosBusquedaCFE->fechaEnvioHasta;
                $signoCoonsulta = '&';
            }

            if ((int)$parametrosBusquedaCFE->registrosPagina != 0) {
                $MBA005_URL .= $signoCoonsulta . 'registrosPagina=' . $parametrosBusquedaCFE->registrosPagina;
                $signoCoonsulta = '&';
            }

            if ((int)$parametrosBusquedaCFE->numeroPagina != 0) {
                $MBA005_URL .= $signoCoonsulta . 'numeroPagina=' . $parametrosBusquedaCFE->numeroPagina;
            }

            $response = $this->httpClient
                ->withToken($token['access_token'])
                ->withOptions(['verify' => false])
                ->get($MBA005_URL);
            return [
                'status' => $response->getStatusCode(),
                'data'   => $response->body(),
            ];
        } catch (\Exception $e) {
            $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 500;

            return [
                'status' => $statusCode,
                'error'  => $e->getMessage(),
            ];
        }
    }

    public function solicitudEnvioFormatoCfeMBA011($xmlData) {
        $MBA011_URL = IntercomConstants::MBA_CFE011_CFE;

        try {
            $token = $this->getAccessToken();

            $response = $this->httpClient
                ->withToken($token['access_token'])
                ->withOptions(['verify' => false])
                ->withBody($xmlData, 'application/xml')
                ->post($MBA011_URL);

            return [
                'status' => $response->getStatusCode(),
                'data'   => $response->body(),
            ];
        } catch (\Exception $e) {
            $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 500;

            return [
                'status' => $statusCode,
                'error'  => $e->getMessage(),
            ];
        }
    }

    public function solicitudEnvioFormatoPfiMBA011($xmlData) {
        $MBA011_URL = IntercomConstants::MBA_CFE011_PFI;

        try {
            $token = $this->getAccessToken();

            $response = $this->httpClient
                ->withToken($token['access_token'])
                ->withOptions(['verify' => false])
                ->withBody($xmlData, 'application/xml')
                ->post($MBA011_URL);

            return [
                'status' => $response->getStatusCode(),
                'data'   => $response->body(),
            ];
        } catch (\Exception $e) {
            $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 500;

            return [
                'status' => $statusCode,
                'error'  => $e->getMessage(),
            ];
        }
    }

    public function solicitudEnvioFormatoCfeMBA023($xmlData) {
        $MBA023_URL = IntercomConstants::MBA_CFE023_CFE;
        try {
            $token = $this->getAccessToken();
            $response = $this->httpClient
                ->withToken($token['access_token'])
                ->withOptions(['verify' => false])
                ->withBody($xmlData, 'application/xml')
                ->post($MBA023_URL);

            return [
                'status' => $response->getStatusCode(),
                'data'   => $response->body(),
            ];
        } catch (\Exception $e) {
            $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 500;

            return [
                'status' => $statusCode,
                'error'  => $e->getMessage(),
            ];
        }
    }

    public function solicitudEnvioFormatoPfiMBA023($xmlData) {
        $MBA023_URL = IntercomConstants::MBA_CFE023_PFI;
        try {
            $token = $this->getAccessToken();
            $response = $this->httpClient
                ->withToken($token['access_token'])
                ->withOptions(['verify' => false])
                ->withBody($xmlData, 'application/xml')
                ->post($MBA023_URL);

            return [
                'status' => $response->getStatusCode(),
                'data'   => $response->body(),
            ];
        } catch (\Exception $e) {
            $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 500;

            return [
                'status' => $statusCode,
                'data'  => $e->getMessage(),
            ];
        }
    }

}
