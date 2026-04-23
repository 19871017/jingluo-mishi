<?php
namespace app\controller\Admin;

use app\services\MarketService;
use app\controller\BaseController;

class MarketController extends BaseController
{
    public function listings()
    {
        $page = (int) $this->request->param('page', 1);
        $limit = (int) $this->request->param('limit', 20);
        $status = $this->request->param('status', '');

        $query = \app\model\MarketListing::with(['user', 'images']);

        if ($status) {
            $query->where('status', $status);
        }

        $total = $query->count();
        $list = $query->order('created_at', 'desc')
            ->page($page, $limit)
            ->select();

        return $this->success([
            'total' => $total,
            'list' => $list,
        ]);
    }

    public function audit(int $id)
    {
        $status = $this->request->param('status');

        if (!in_array($status, ['approved', 'rejected'])) {
            return $this->error('Invalid status');
        }

        $result = MarketService::audit($id, $status);

        if (!$result) {
            return $this->error('Listing not found', 404);
        }

        return $this->success(null, 'Audited successfully');
    }

    public function delete(int $id)
    {
        $result = MarketService::delete($id);

        if (!$result) {
            return $this->error('Listing not found', 404);
        }

        return $this->success(null, 'Deleted successfully');
    }

    public function setFeatured(int $id)
    {
        $featured = (bool) $this->request->param('featured', false);

        $result = MarketService::setFeatured($id, $featured);

        if (!$result) {
            return $this->error('Listing not found', 404);
        }

        return $this->success(null, 'Updated successfully');
    }
}
