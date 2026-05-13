<?php

namespace Modules\Intercom\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Http;
use Modules\Intercom\Models\IntercomOAuthToken;

class BaseIntercomService
{
    protected $clientId;
    protected $clientSecret;
    protected $tokenUrl;
    protected $realm;

    public function __construct()
    {
        $this->initializeConfig();
        $this->httpClient = Http::withOptions([
            'base_uri' => config('intercom.intercom_host'),
            'timeout' => 10.0,
        ]);
    }

    public function initializeConfig(): void
    {
        $this->clientId = config('intercom.client_id');
        $this->clientSecret = config('intercom.client_secret');
        $this->authHost = config('intercom.auth_host');
        $this->realm = config('intercom.realm');
        $this->tokenUrl = $this->authHost . '/realms/' . $this->realm . '/protocol/openid-connect/token';
    }

    public function getAccessToken(): array
    {
        $token = $this->findValidToken();

        if(!$token) {
            $token = $this->fetchAndStoreAccessToken();
        }

        return $this->formatTokenData($token);
    }

    private function findValidToken(): ?IntercomOAuthToken
    {
        try {
        return IntercomOAuthToken::where('client_id', $this->clientId)
            ->where('expires_in', '>', now())
            ->first();
        } catch (QueryException $e) {
            $error = $e->getMessage();
            $sql = $e->getSql();
            $bindings = $e->getBindings();
            throw new \Exception('Error SQL: Mesagge: ' . $error .' -> SQL: '. $sql . ' ->bindings: '. $bindings);
        }
    }

    private function fetchAndStoreAccessToken(): IntercomOAuthToken
    {
        $response = $this->requestAccessToken();

        if ($response->failed()) {
            throw new \Exception('Error fetching access token: ' . $response->body());
        }

        return $this->storeAccessToken($response->json());
    }

    private function requestAccessToken(): \Illuminate\Http\Client\Response
    {
        return Http::asForm()->post($this->tokenUrl, [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type' => 'client_credentials',
        ]);
    }

    private function storeAccessToken(array $data): IntercomOAuthToken
    {
        return IntercomOAuthToken::create([
            'client_id' => $this->clientId,
            'access_token' => $data['access_token'],
            'expires_in' => now()->addSeconds($data['expires_in'] - 30),
        ]);
    }

    private function formatTokenData($token)
    {
        return [
            'access_token' => $token->access_token,
            'expires_in' => $token->expires_in,
        ];
    }

    protected function genFormatCode(): ?string
    {
        $randomNumber = str_pad(mt_rand(9999, 99999), 5, '0', STR_PAD_LEFT);
        $originCountry = 'EC';

        return $randomNumber . $originCountry;
    }
}
