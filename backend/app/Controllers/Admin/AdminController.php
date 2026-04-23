<?php
namespace app\controller\Admin;

use app\model\Admin as AdminModel;
use app\controller\BaseController;

class AdminController extends BaseController
{
    public function list()
    {
        $page = (int) $this->request->param('page', 1);
        $limit = (int) $this->request->param('limit', 20);

        $query = AdminModel::order('created_at', 'desc');

        $total = $query->count();
        $list = $query->page($page, $limit)->select();

        return $this->success([
            'total' => $total,
            'list' => $list,
        ]);
    }

    public function create()
    {
        $admin = $this->request->admin;
        if (!$admin || !$admin->isSuper()) {
            return $this->error('Unauthorized', 401);
        }

        $username = $this->request->param('username');
        $password = $this->request->param('password');
        $role = $this->request->param('role', 'normal');

        if (empty($username) || empty($password)) {
            return $this->error('Username and password are required');
        }

        if (AdminModel::where('username', $username)->find()) {
            return $this->error('Username already exists');
        }

        $newAdmin = AdminModel::create([
            'username' => $username,
            'password' => $password,
            'role' => $role,
        ]);

        return $this->success([
            'id' => $newAdmin->id,
            'username' => $newAdmin->username,
            'role' => $newAdmin->role,
        ], 'Created successfully', 201);
    }

    public function update(int $id)
    {
        $admin = $this->request->admin;
        if (!$admin || !$admin->isSuper()) {
            return $this->error('Unauthorized', 401);
        }

        $targetAdmin = AdminModel::find($id);
        if (!$targetAdmin) {
            return $this->error('Admin not found', 404);
        }

        $data = $this->request->only(['username', 'role']);
        if (!empty($data['password'])) {
            $data['password'] = $data['password'];
        }

        $targetAdmin->save($data);

        return $this->success(null, 'Updated successfully');
    }

    public function delete(int $id)
    {
        $admin = $this->request->admin;
        if (!$admin || !$admin->isSuper()) {
            return $this->error('Unauthorized', 401);
        }

        $targetAdmin = AdminModel::find($id);
        if (!$targetAdmin) {
            return $this->error('Admin not found', 404);
        }

        if ($targetAdmin->id === $admin->id) {
            return $this->error('Cannot delete yourself');
        }

        $targetAdmin->delete();

        return $this->success(null, 'Deleted successfully');
    }
}
