<?php
namespace app\model;

use think\Model;

class Admin extends Model
{
    protected $table = 'admin';
    protected $pk = 'id';

    protected $fillable = ['username', 'password', 'role'];

    protected $hidden = ['password'];

    public function setPasswordAttr($value)
    {
        return password_hash($value, PASSWORD_BCRYPT);
    }

    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }

    public function isSuper(): bool
    {
        return $this->role === 'super';
    }
}
