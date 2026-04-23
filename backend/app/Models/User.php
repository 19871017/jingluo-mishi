<?php
namespace app\model;

use think\Model;

class User extends Model
{
    protected $table = 'user';
    protected $pk = 'id';

    protected $fillable = ['openid', 'nickname', 'avatar'];

    protected $hidden = [];

    protected $json = [];
    protected $jsonAssoc = true;

    public function interactions()
    {
        return $this->hasMany(UserInteraction::class, 'user_id');
    }

    public function marketListings()
    {
        return $this->hasMany(MarketListing::class, 'user_id');
    }

    public function isLiked(int $targetType, int $targetId): bool
    {
        return UserInteraction::where('user_id', $this->id)
            ->where('target_type', $targetType)
            ->where('target_id', $targetId)
            ->where('action_type', 'like')
            ->find() !== null;
    }

    public function isCollected(int $targetType, int $targetId): bool
    {
        return UserInteraction::where('user_id', $this->id)
            ->where('target_type', $targetType)
            ->where('target_id', $targetId)
            ->where('action_type', 'collect')
            ->find() !== null;
    }

    public function isFollowed(int $targetType, int $targetId): bool
    {
        return UserInteraction::where('user_id', $this->id)
            ->where('target_type', $targetType)
            ->where('target_id', $targetId)
            ->where('action_type', 'follow')
            ->find() !== null;
    }
}
