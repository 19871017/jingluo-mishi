<?php
namespace app\controller;

use app\model\HomeBanner;
use app\model\HomeAd;
use app\model\Script;
use app\services\ScriptService;

class HomeController extends BaseController
{
    public function index()
    {
        $banners = HomeBanner::order('sort_order', 'asc')->limit(5)->select();
        $ads = HomeAd::order('sort_order', 'asc')->limit(3)->select();
        $scripts = Script::with(['brand', 'images'])
            ->where('status', 'approved')
            ->order('like_count', 'desc')
            ->limit(10)
            ->select();

        return $this->success([
            'banners' => $banners->map(fn($b) => $b->toApiData())->toArray(),
            'ads' => $ads->map(fn($a) => $a->toApiData())->toArray(),
            'scripts' => array_map(fn($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'thumbnail' => $s->thumbnail,
                'like_count' => $s->like_count,
                'brand' => $s->brand ? ['id' => $s->brand->id, 'name' => $s->brand->name] : null,
            ], $scripts->toArray()),
        ]);
    }
}
