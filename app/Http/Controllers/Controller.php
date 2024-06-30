<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

abstract class Controller
{
    public static function successResponse(string $message = '', int $code = 200, array|bool $data = [])
    {
        $response = [
            'success' => true,
        ];

        if (! empty($message)) {
            $response['message'] = $message;
        }

        if (! empty($data)) {
            $response['data'] = $data;
        }

        return response($response, $code);
    }

    public static function failResponse(array $data = [])
    {
        dd($data, \Illuminate\Support\Facades\Request::all());
        return view('errorPage', $data);
    }
}
