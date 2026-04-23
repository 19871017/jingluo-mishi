<?php
namespace app\controller;

use app\model\Script;
use app\model\ScriptImage;
use app\services\ScriptService;

class ScriptController extends BaseController
{
    public function search()
    {
        $keyword = $this->request->param('keyword', '');
        $page = (int) $this->request->param('page', 1);
        $limit = (int) $this->request->param('limit', 20);

        $filters = [];
        if ($keyword) {
            $filters['keyword'] = $keyword;
        }

        $result = ScriptService::getList($filters, $page, $limit);

        $list = array_map(function ($script) {
            $authInfo = $script->auth_info ?? [];
            $cityPrices = $authInfo['city_prices'] ?? [];
            $minPrice = min(array_values($cityPrices)) ?: null;
            $maxPrice = max(array_values($cityPrices)) ?: null;

            return [
                'id' => $script->id,
                'name' => $script->name,
                'thumbnail' => $script->thumbnail,
                'like_count' => $script->like_count,
                'price_range' => $minPrice && $maxPrice
                    ? ($minPrice === $maxPrice ? "{$minPrice}元" : "{$minPrice}-{$maxPrice}元")
                    : '价格面议',
            ];
        }, $result['list']);

        return $this->success([
            'total' => $result['total'],
            'list' => $list,
        ]);
    }

    public function detail(int $id)
    {
        $userId = $this->request->user->id ?? null;
        $script = ScriptService::getDetail($id, $userId);

        if (!$script) {
            return $this->error('Script not found', 404);
        }

        $data = $script->toApiData();

        if ($userId) {
            $data['is_liked'] = $script->isLiked('script', $id);
            $data['is_collected'] = $script->isCollected('script', $id);
        }

        return $this->success($data);
    }

    public function like(int $id)
    {
        $user = $this->request->user;
        if (!$user) {
            return $this->error('Unauthorized', 401);
        }

        $script = Script::find($id);
        if (!$script) {
            return $this->error('Script not found', 404);
        }

        $script->incrementLikes();

        return $this->success(null, 'Liked successfully');
    }

    public function unlike(int $id)
    {
        $user = $this->request->user;
        if (!$user) {
            return $this->error('Unauthorized', 401);
        }

        $script = Script::find($id);
        if (!$script) {
            return $this->error('Script not found', 404);
        }

        $script->decrementLikes();

        return $this->success(null, 'Unliked successfully');
    }

    public function collect(int $id)
    {
        $user = $this->request->user;
        if (!$user) {
            return $this->error('Unauthorized', 401);
        }

        $script = Script::find($id);
        if (!$script) {
            return $this->error('Script not found', 404);
        }

        return $this->success(null, 'Collected successfully');
    }
}
