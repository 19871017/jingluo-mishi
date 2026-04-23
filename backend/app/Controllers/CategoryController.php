<?php
namespace app\controller;

use app\model\Category;
use app\services\ScriptService;

class CategoryController extends BaseController
{
    public function list()
    {
        $categories = Category::order('sort_order', 'asc')->select();

        $result = array_map(function ($cat) {
            return [
                'id' => $cat->id,
                'name' => $cat->name,
                'count' => $cat->script_count,
            ];
        }, $categories->toArray());

        return $this->success(['list' => $result]);
    }

    public function scripts(int $id)
    {
        $category = Category::find($id);
        if (!$category) {
            return $this->error('Category not found', 404);
        }

        $filters = [
            'category_id' => $id,
        ];

        $page = (int) $this->request->param('page', 1);
        $limit = (int) $this->request->param('limit', 20);

        $scripts = ScriptService::getList($filters, $page, $limit);

        $ads = [];

        return $this->success([
            'ads' => $ads,
            'scripts' => array_map(fn($s) => $s->toApiData(), $scripts['list']),
            'filters' => [
                'price_range' => ['1万以下', '1-5万', '5-10万', '10万以上'],
                'players' => ['2人', '3-4人', '5-6人', '7人以上'],
                'horror_level' => ['微恐', '中恐', '重恐'],
            ],
        ]);
    }
}
