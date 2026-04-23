<?php
namespace app\model;

use think\Model;

class UserInteraction extends Model
{
    protected $table = 'user_interaction';
    protected $pk = 'id';

    protected $fillable = ['user_id', 'target_type', 'target_id', 'action_type'];

    protected $type = [
        'target_id' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeLikes($query)
    {
        return $query->where('action_type', 'like');
    }

    public function scopeCollects($query)
    {
        return $query->where('action_type', 'collect');
    }

    public function scopeFollows($query)
    {
        return $query->where('action_type', 'follow');
    }

    public function scopeScripts($query)
    {
        return $query->where('target_type', 'script');
    }

    public function scopeBrands($query)
    {
        return $query->where('target_type', 'brand');
    }
}
