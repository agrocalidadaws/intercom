<?php

namespace Modules\Intercom\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Laravel\Passport\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class LoginController extends Controller
{

    public function login(Request $request)
    {
        if (!$request->all()) {
            return response()->json([
                'message' => 'No se enviaron los datos de autenticación.'
            ], 400);
        }

        $contentType = $request->header('Content-Type');

        if (!str_starts_with($contentType, 'application/x-www-form-urlencoded')) {
            return response()->json([
                'error' => 'Unsupported Content-Type. Must be application/x-www-form-urlencoded.'
            ], 415);
        }

        $request->validate([
            'client_id' => 'string',
            'client_secret' => 'string',
        ]);

        $oauthClient = Client::where('password_client', true)->first();

        $tokenUrl = config('services.passport.token_url');

        try {
            $response = Http::asForm()->post($tokenUrl, [
                'grant_type' => 'password',
                'Accept' => 'application/json',
                'client_id' => $oauthClient->id,
                'client_secret' => $oauthClient->secret,
                'username' => $request->client_id,
                'password' => $request->client_secret,
                'scope' => '*',
            ]);

            return response()->json(json_decode((string) $response->getBody(), true));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        }

        return $user;
    }
}
