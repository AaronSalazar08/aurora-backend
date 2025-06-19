<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CorsMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $headers = [
            'Access-Control-Allow-Origin' => 'http://localhost:5173',
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, PATCH, DELETE, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With',
            'Access-Control-Allow-Credentials' => 'false',
        ];

        // Si es preflight, devolvemos 204 con los headers y sin correr nada más
        if ($request->getMethod() === 'OPTIONS') {
            return response('', 204)->withHeaders($headers);
        }

        // Para peticiones reales, corremos el siguiente middleware/controlador
        $response = $next($request);

        // Y añadimos los headers CORS a la respuesta
        return $response->withHeaders($headers);
    }
}
