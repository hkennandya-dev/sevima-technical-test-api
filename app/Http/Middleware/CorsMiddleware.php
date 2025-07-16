<?php

namespace App\Http\Middleware;

use Closure;

class CorsMiddleware
{
    protected $allowedOrigins = [
        'http://localhost:3000',
        'https://sevima-instaapp.hkennandya.yuivastudio.com',
    ];

    public function handle($request, Closure $next)
    {
        $origin = $request->headers->get('Origin');

        $response = $next($request);

        if (in_array($origin, $this->allowedOrigins)) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
        }

        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');

        if ($request->getMethod() === "OPTIONS") {
            return response('', 200, $response->headers->all());
        }

        return $response;
    }
}
