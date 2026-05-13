<?php

namespace Modules\Intercom\Exceptions;

class Handler extends ExceptionHandler
{
    protected $dontReport = [];

    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Token inválido, expirado o no proporcionado.'
            ], 401);
        }

        return redirect()->guest(route('login'));
    }

    public function render($request, Throwable $exception)
    {
        if ($exception instanceof OAuthServerException) {
            $message = strtolower($exception->getMessage());

            if (str_contains($message, 'expired')) {
                return response()->json([
                    'error' => 'El token ha expirado. Por favor, vuelva a autenticarse.'
                ], 401);
            }

            if (str_contains($message, 'invalid')) {
                return response()->json([
                    'error' => 'El token es inválido. Verifique sus credenciales.'
                ], 401);
            }

            return response()->json([
                'error' => 'Error de autenticación OAuth: ' . $exception->getMessage()
            ], 401);
        }

        return parent::render($request, $exception);
    }
}
