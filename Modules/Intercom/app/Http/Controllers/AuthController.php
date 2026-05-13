<?php

namespace Modules\Intercom\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Intercom\Services\BaseIntercomService;

class AuthController extends Controller
{
    public function getToken()
    {
        $service = new BaseIntercomService();

        $token = $service->getAccessToken();

        return $token;
    }
}
