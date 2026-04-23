<?php
namespace app\middleware;

use app\model\Admin;
use app\services\JwtService;
use think\Request;

class AdminMiddleware
{
    public function handle(Request $request, \Closure $next)
    {
        $token = $request->header('Admin-Token', '');

        if (empty($token)) {
            return json(['code' => 401, 'msg' => 'Unauthorized'], 401);
        }

        try {
            $payload = JwtService::decode($token);
            $admin = Admin::find($payload['admin_id']);

            if (!$admin) {
                return json(['code' => 401, 'msg' => 'Admin not found'], 401);
            }

            $request->admin = $admin;
            return $next($request);
        } catch (\Exception $e) {
            return json(['code' => 401, 'msg' => 'Invalid token'], 401);
        }
    }
}
