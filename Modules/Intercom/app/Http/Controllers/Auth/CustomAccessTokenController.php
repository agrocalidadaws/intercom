<?php

namespace Modules\Intercom\Http\Controllers\Auth;

use Laravel\Passport\Http\Controllers\AccessTokenController;
use Psr\Http\Message\ServerRequestInterface;
use Illuminate\Http\JsonResponse;

class CustomAccessTokenController extends AccessTokenController
{
    public function issueToken(ServerRequestInterface $request)
    {
        // Esto devuelve una instancia de Illuminate\Http\Response en Laravel 11
        $laravelResponse = parent::issueToken($request);

        // Obtener el contenido de la respuesta (JSON)
        $data = json_decode($laravelResponse->getContent(), true);

        // Eliminar refresh_token si existe
        unset($data['refresh_token']);

        // Devolver nueva respuesta JSON sin refresh_token
        return new JsonResponse($data, $laravelResponse->getStatusCode(), [
            'Content-Type' => 'application/json',
        ]);
        
    }
}