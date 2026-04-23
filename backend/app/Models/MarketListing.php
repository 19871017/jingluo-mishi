<?php
namespace app\model;

use think\Model;

class MarketListing extends Model
{
    protected $table = 'market_listing';
    protected $pk = 'id';

    protected $fillable = [
        'user_id', 'type', 'title', 'description', 'price', 'status', 'is_featured'
    ];

    protected $type = [
        'price' => 'float',
        'is_featured' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function images()
    {
        return $this->hasMany(MarketImage::class, 'listing_id')->order('sort_order asc');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', 1)->where('status', 'approved');
    }

    public function scopeBuy($query)
    {
        return $query->where('type', 'buy');
    }

    public function scopeSell($query)
    {
        return $query->where('type', 'sell');
    }

    public function toApiData(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $this->title,
            'description' => $this->description,
            'price' => $this->price,
            'is_featured' => $this->is_featured,
            'images' => $this->images->map(fn($img) => $img->url)->toArray(),
            'user' => $this->user ? [
                'id' => $this->user->id,
                'nickname' => $this->user->nickname,
                'avatar' => $this->user->avatar,
            ] : null,
            'created_at' => $this->created_at,
        ];
    }
}
