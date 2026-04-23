<?php
namespace app\model;

use think\Model;

class Script extends Model
{
    protected $table = 'script';
    protected $pk = 'id';

    protected $fillable = [
        'brand_id', 'category_id', 'name', 'alias', 'create_date',
        'min_players', 'max_players', 'duration', 'type', 'authorizer',
        'description', 'thumbnail', 'theme_attrs', 'detail_attrs',
        'auth_info', 'status', 'view_count', 'like_count',
        'video_url', 'detail_content'
    ];

    protected $json = ['theme_attrs', 'detail_attrs', 'auth_info'];
    protected $jsonAssoc = true;

    protected $type = [
        'create_date' => 'date',
        'min_players' => 'integer',
        'max_players' => 'integer',
        'duration' => 'integer',
        'view_count' => 'integer',
        'like_count' => 'integer',
        'theme_attrs' => 'json',
        'detail_attrs' => 'json',
        'auth_info' => 'json',
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function images()
    {
        return $this->hasMany(ScriptImage::class, 'script_id')->order('sort_order asc');
    }

    public function incrementViews(): void
    {
        $this->increment('view_count');
        $this->brand->incrementViews();
    }

    public function incrementLikes(): void
    {
        $this->increment('like_count');
        $this->brand->incrementLikes();
    }

    public function decrementLikes(): void
    {
        $this->where('id', $this->id)->where('like_count', '>', 0)->dec('like_count');
        $this->brand->where('id', $this->brand_id)->where('total_likes', '>', 0)->dec('total_likes');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function toApiData(): array
    {
        $authInfo = $this->auth_info ?? [];
        $cityPrices = $authInfo['city_prices'] ?? [];
        $priceTier1 = !empty($cityPrices) ? min(array_values($cityPrices)) : 0;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'alias' => $this->alias,
            'brand' => $this->brand ? [
                'id' => $this->brand->id,
                'name' => $this->brand->name,
            ] : null,
            'category' => $this->category ? [
                'id' => $this->category->id,
                'name' => $this->category->name,
            ] : null,
            'create_date' => $this->create_date,
            'min_players' => $this->min_players,
            'max_players' => $this->max_players,
            'duration' => $this->duration,
            'type' => $this->type,
            'authorizer' => $this->authorizer,
            'description' => $this->description,
            'thumbnail' => $this->thumbnail,
            'video_url' => $this->video_url ?? '',
            'detail_content' => $this->detail_content ?? '',
            'theme_attrs' => $this->theme_attrs ?? [],
            'detail_attrs' => $this->detail_attrs ?? [],
            'auth_info' => $authInfo,
            'images' => $this->images->map(fn($img) => ['url' => $img->url])->toArray(),
            'view_count' => $this->view_count,
            'like_count' => $this->like_count,
            'price_tier1' => $priceTier1,
        ];
    }
}
