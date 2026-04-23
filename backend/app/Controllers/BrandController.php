<?php
namespace app\controller;

use app\model\Brand;
use app\services\BrandService;

class BrandController extends BaseController
{
    public function list()
    {
        $page = (int) $this->request->param('page', 1);
        $limit = (int) $this->request->param('limit', 20);

        $result = BrandService::getList($page, $limit, 'approved');

        $list = array_map(function ($brand) {
            return [
                'id' => $brand->id,
                'name' => $brand->name,
                'logo' => $brand->logo,
                'follower_count' => $brand->follower_count,
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
        $brand = BrandService::getDetail($id, $userId);

        if (!$brand) {
            return $this->error('Brand not found', 404);
        }

        return $this->success([
            'id' => $brand->id,
            'name' => $brand->name,
            'logo' => $brand->logo,
            'description' => $brand->description,
            'total_authorizations' => $brand->total_authorizations,
            'total_views' => $brand->total_views,
            'total_likes' => $brand->total_likes,
            'follower_count' => $brand->follower_count,
            'is_followed' => $brand->is_followed ?? false,
            'hot_scripts' => array_map(fn($s) => $s->toApiData(), $brand->approvedScripts->take(5)->toArray()),
            'all_scripts' => array_map(fn($s) => $s->toApiData(), $brand->approvedScripts->toArray()),
        ]);
    }

    public function follow(int $id)
    {
        $user = $this->request->user;
        if (!$user) {
            return $this->error('Unauthorized', 401);
        }

        $result = BrandService::follow($user->id, $id);

        if (!$result) {
            return $this->error('Already followed', 400);
        }

        return $this->success(null, 'Followed successfully');
    }

    public function unfollow(int $id)
    {
        $user = $this->request->user;
        if (!$user) {
            return $this->error('Unauthorized', 401);
        }

        $result = BrandService::unfollow($user->id, $id);

        if (!$result) {
            return $this->error('Not following', 400);
        }

        return $this->success(null, 'Unfollowed successfully');
    }
}
