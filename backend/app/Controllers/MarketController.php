<?php
namespace app\controller;

use app\services\MarketService;

class MarketController extends BaseController
{
    public function list()
    {
        $type = $this->request->param('type', 'sell');
        $page = (int) $this->request->param('page', 1);
        $limit = (int) $this->request->param('limit', 20);
        $sort = $this->request->param('sort', 'latest');

        $result = MarketService::getList($type, $page, $limit, $sort);

        return $this->success([
            'featured' => array_map(fn($l) => $l->toApiData(), $result['featured']),
            'listings' => array_map(fn($l) => $l->toApiData(), $result['listings']),
            'total' => $result['total'],
        ]);
    }

    public function detail(int $id)
    {
        $listing = MarketService::getDetail($id);

        if (!$listing || $listing->status !== 'approved') {
            return $this->error('Listing not found', 404);
        }

        return $this->success($listing->toApiData());
    }

    public function create()
    {
        $user = $this->request->user;
        if (!$user) {
            return $this->error('Unauthorized', 401);
        }

        $data = $this->request->only(['type', 'title', 'description', 'price', 'images']);

        if (empty($data['title'])) {
            return $this->error('Title is required');
        }

        $listing = MarketService::create($user->id, $data);

        return $this->success($listing->toApiData(), 'Created successfully', 201);
    }
}
