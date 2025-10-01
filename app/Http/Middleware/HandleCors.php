<?php

namespace App\Http\Middleware;

use Closure;

class HandleCors
{
    /**
     * Handle an incoming request and apply CORS headers.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Illuminate\Http\Response
     */
    public function handle($request, Closure $next)
    {
        $allowedOrigins = ['*']; // Replace * with specific origins if needed
        $allowedMethods = 'GET, POST, PUT, DELETE, OPTIONS';
        $allowedHeaders = 'Content-Type, Authorization, X-Requested-With';

        $response = $next($request);

        if ($request->isMethod('OPTIONS')) {
            return response('', 200)
                ->header('Access-Control-Allow-Origin', implode(', ', $allowedOrigins))
                ->header('Access-Control-Allow-Methods', $allowedMethods)
                ->header('Access-Control-Allow-Headers', $allowedHeaders);
        }

        return $response
            ->header('Access-Control-Allow-Origin', implode(', ', $allowedOrigins))
            ->header('Access-Control-Allow-Methods', $allowedMethods)
            ->header('Access-Control-Allow-Headers', $allowedHeaders);
    }
}
