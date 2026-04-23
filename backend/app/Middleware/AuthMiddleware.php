<?php
namespace app\middleware;

use app\model\User;
use app\services\JwtService;
use think\Request;

class AuthMiddleware
{
    public function handle(Request $request, \Closure $next)
    {
        $token = $request->header('Authorization', '');
        if (empty($token)) {
            $token = $request->param('token', '');
        }

        if (empty($token)) {
            return json(['code' => 401, 'msg' => 'Unauthorized'], 401);
        }

        $token = str_replace('Bearer ', '', $token);

        try {
            $payload = JwtService::decode($token);
            $user = User::find($payload['user_id']);

            if (!$user) {
                return json(['code' => 401, 'msg' => 'User not found'], 401);
            }

            $request->user = $user;
            return $next($request);
        } catch (\Exception $e) {
            return json(['code' => 401, 'msg' => 'Invalid token'], 401);
        }
    }
}
