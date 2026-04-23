<?php
namespace app\controller\Admin;

use app\model\Script;
use app\model\ScriptImage;
use app\services\ScriptService;
use app\controller\BaseController;

class ScriptController extends BaseController
{
    public function list()
    {
        $page = (int) $this->request->param('page', 1);
        $limit = (int) $this->request->param('limit', 20);
        $status = $this->request->param('status', '');

        $query = Script::with(['brand', 'category', 'images']);

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

    public function create()
    {
        $data = $this->request->only([
            'brand_id', 'category_id', 'name', 'alias', 'create_date',
            'min_players', 'max_players', 'duration', 'type', 'authorizer',
            'description', 'thumbnail', 'video_url', 'detail_content',
            'theme_attrs', 'detail_attrs', 'auth_info', 'status', 'images'
        ]);

        if (empty($data['name']) || empty($data['brand_id']) || empty($data['category_id'])) {
            return $this->error('Name, brand_id and category_id are required');
        }

        $script = ScriptService::create($data);

        return $this->success($script->toApiData(), 'Created successfully', 201);
    }

    public function update(int $id)
    {
        $script = Script::find($id);
        if (!$script) {
            return $this->error('Script not found', 404);
        }

        $data = $this->request->only([
            'brand_id', 'category_id', 'name', 'alias', 'create_date',
            'min_players', 'max_players', 'duration', 'type', 'authorizer',
            'description', 'thumbnail', 'video_url', 'detail_content',
            'theme_attrs', 'detail_attrs', 'auth_info', 'status', 'images'
        ]);

        ScriptService::update($id, $data);

        return $this->success(null, 'Updated successfully');
    }

    public function delete(int $id)
    {
        $result = ScriptService::delete($id);

        if (!$result) {
            return $this->error('Script not found', 404);
        }

        return $this->success(null, 'Deleted successfully');
    }

    public function audit(int $id)
    {
        $status = $this->request->param('status');

        if (!in_array($status, ['approved', 'rejected'])) {
            return $this->error('Invalid status');
        }

        $result = ScriptService::audit($id, $status);

        if (!$result) {
            return $this->error('Script not found', 404);
        }

        return $this->success(null, 'Audited successfully');
    }

    public function restore(int $id)
    {
        $script = Script::onlyTrashed()->find($id);
        if (!$script) {
            return $this->error('Script not found', 404);
        }

        $script->restore();

        return $this->success(null, 'Restored successfully');
    }

    public function permanentDelete(int $id)
    {
        $script = Script::onlyTrashed()->find($id);
        if (!$script) {
            return $this->error('Script not found', 404);
        }

        $script->forceDelete();

        return $this->success(null, 'Permanently deleted');
    }
}
