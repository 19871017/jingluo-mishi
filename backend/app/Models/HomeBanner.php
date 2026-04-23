<?php
namespace app\model;

use think\Model;

class HomeBanner extends Model
{
    protected $table = 'home_banner';
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
