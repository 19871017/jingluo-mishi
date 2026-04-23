<?php
namespace app\model;

use think\Model;

class ScriptImage extends Model
{
    protected $table = 'script_image';
    protected $pk = 'id';

    protected $fillable = ['script_id', 'url', 'sort_order'];

    protected $type = [
        'sort_order' => 'integer',
    ];

    public function script()
    {
        return $this->belongsTo(Script::class, 'script_id');
    }
}
