<?php
namespace app\model;

use think\Model;

class HomeAd extends Model
{
    protected $table = 'home_ad';
    protected $pk = 'id';

    protected $fillable = ['image', 'link', 'sort_order'];

    protected $type = [
        'sort_order' => 'integer',
    ];

    public function toApiData(): array
    {
        return [
            'id' => $this->id,
            'image' => $this->image,
            'link' => $this->link,
        ];
    }
}
