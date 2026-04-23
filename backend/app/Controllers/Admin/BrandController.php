<?php
namespace app\controller\Admin;

use app\model\Brand;
use app\services\BrandService;
use app\controller\BaseController;

class BrandController extends BaseController
{
    public function list()
    {
        $page = (int) $this->request->param('page', 1);
        $limit = (int) $this->request->param('limit', 20);
        $status = $this->request->param('status', '');

        $result = BrandService::getList($page, $limit, $status ?: null);

        return $this->success([
            'total' => $result['total'],
            'list' => $result['list'],
        ]);
    }

    public function detail(int $id)
    {
        $brand = Brand::with(['approvedScripts.images'])->find($id);

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
            'status' => $brand->status,
            'scripts' => array_map(fn($s) => $s->toApiData(), $brand->approvedScripts->toArray()),
        ]);
    }

    public function audit(int $id)
    {
        $status = $this->request->param('status');

        if (!in_array($status, ['approved', 'rejected'])) {
            return $this->error('Invalid status');
        }

        $result = BrandService::audit($id, $status);

        if (!$result) {
            return $this->error('Brand not found', 404);
        }

        return $this->success(null, 'Audited successfully');
    }
}
