<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\Response as IlluminateResponse;

abstract class Controller
{

    public function response($data, $statusCode)
    {
        return Response::make($data, $statusCode, [
            'Content-Type' => 'application/text'
        ]);
    }
    public function responseXML($xml, $statusCode = 200)
    {
        return Response::make($xml, $statusCode, [
            'Content-Type' => 'application/xml'
        ]);
    }

    public function responseJson($data, $statusCode = 200)
    {
        return Response::json($data, $statusCode);
    }

    public function responseError(\Exception $e)
    {
        $message = $e->getMessage() ? $e->getMessage() : "Unexpected error occurred.";

        $arr_message = explode("\n", $message);

        return Response::json([
            "error" => $arr_message,
            "error_code" => $e->getCode()
        ], IlluminateResponse::HTTP_INTERNAL_SERVER_ERROR);
    }
}
