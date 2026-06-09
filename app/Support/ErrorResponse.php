<?php

namespace App\Support;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ErrorResponse
{
    public static function view(int $status, Request $request, array $data = []): Response
    {
        $view = match ($status) {
            401 => 'errors.401',
            403 => 'errors.403',
            404 => 'errors.404',
            419 => 'errors.419',
            429 => 'errors.429',
            503 => 'errors.503',
            default => 'errors.500',
        };

        return response()->view($view, array_merge(['status' => $status], $data), $status);
    }

    public static function json(int $status): Response
    {
        return response()->json([
            'message' => match ($status) {
                401 => 'Sign in required.',
                403 => 'Forbidden.',
                404 => 'Not found.',
                419 => 'Page expired.',
                429 => 'Too many requests.',
                503 => 'Service unavailable.',
                default => 'Server error.',
            },
        ], $status);
    }
}
