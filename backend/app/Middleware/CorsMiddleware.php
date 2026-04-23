<?php
namespace app\middleware;

use think\Request;

class CorsMiddleware
{
    public function handle(Request $request, \Closure $next)
    {
        $response = $next($request);

        $response->header([
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization, Admin-Token, X-Requested-With',
            'Access-Control-Max-Age' => '1728000',
        ]);

        if ($request->method(true) === 'OPTIONS') {
            return response('', 204, $response->getHeader());
        }

        return $response;
    }
}
