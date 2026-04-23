<?php
namespace app\model;

use think\Model;

class Category extends Model
{
    protected $table = 'category';
    protected $pk = 'id';

    protected $fillable = ['name', 'sort_order'];

    protected $json = [];
    protected $jsonAssoc = true;

    public function scripts()
    {
        return $this->hasMany(Script::class, 'category_id');
    }

    public function getScriptCountAttr(): int
    {
        return $this->scripts()->where('status', 'approved')->count();
    }
}
