<?php
namespace app\controller\Admin;

use app\controller\BaseController;
use app\model\Admin;
use app\services\JwtService;

class AuthController extends BaseController
{
    public function login()
    {
        $username = $this->request->param('username');
        $password = $this->request->param('password');

        if (empty($username) || empty($password)) {
            return $this->error('Username and password are required');
        }

        $admin = Admin::where('username', $username)->find();

        if (!$admin || !$admin->verifyPassword($password)) {
            return $this->error('Invalid username or password', 401);
        }

        $token = JwtService::encode(['admin_id' => $admin->id]);

        return $this->success([
            'token' => $token,
            'admin' => [
                'id' => $admin->id,
                'username' => $admin->username,
                'role' => $admin->role,
            ],
        ]);
    }

    public function logout()
    {
        return $this->success(null, 'Logged out successfully');
    }

    public function profile()
    {
        $admin = $this->request->admin;
        if (!$admin) {
            return $this->error('Unauthorized', 401);
        }

        return $this->success([
            'id' => $admin->id,
            'username' => $admin->username,
            'role' => $admin->role,
        ]);
    }

    public function updatePassword()
    {
        $admin = $this->request->admin;
        if (!$admin) {
            return $this->error('Unauthorized', 401);
        }

        $oldPassword = $this->request->param('old_password');
        $newPassword = $this->request->param('new_password');

        if (empty($oldPassword) || empty($newPassword)) {
            return $this->error('Old password and new password are required');
        }

        if (!$admin->verifyPassword($oldPassword)) {
            return $this->error('Invalid old password');
        }

        $admin->password = $newPassword;
        $admin->save();

        return $this->success(null, 'Password updated successfully');
    }
}
