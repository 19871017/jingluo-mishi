<?php
namespace app\model;

use think\Model;

class MarketImage extends Model
{
    protected $table = 'market_image';
    protected $pk = 'id';

    protected $fillable = ['listing_id', 'url', 'sort_order'];

    protected $type = [
        'sort_order' => 'integer',
    ];

    public function listing()
    {
        return $this->belongsTo(MarketListing::class, 'listing_id');
    }
}
