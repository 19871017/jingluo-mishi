<?php
namespace app\model;

use think\Model;

class Brand extends Model
{
    protected $table = 'brand';
    protected $pk = 'id';

    protected $fillable = [
        'name', 'logo', 'description', 'total_authorizations',
        'total_views', 'total_likes', 'follower_count', 'status'
    ];

    protected $json = [];
    protected $jsonAssoc = true;

    protected $type = [
        'status' => 'string'
    ];

    public function scripts()
    {
        return $this->hasMany(Script::class, 'brand_id');
    }

    public function approvedScripts()
    {
        return $this->hasMany(Script::class, 'brand_id')->where('status', 'approved');
    }

    public function incrementViews(): void
    {
        $this->increment('total_views');
    }

    public function incrementLikes(): void
    {
        $this->increment('total_likes');
    }

    public function incrementFollowers(): void
    {
        $this->increment('follower_count');
    }

    public function decrementFollowers(): void
    {
        $this->where('id', $this->id)->where('follower_count', '>', 0)->dec('follower_count');
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
}
