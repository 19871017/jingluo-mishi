<?php
namespace app\services;

use app\model\Script;
use app\model\ScriptImage;
use think\db\Query;

class ScriptService
{
    public static function getList(array $filters = [], int $page = 1, int $limit = 20): array
    {
        $query = Script::with(['brand', 'category', 'images'])
            ->where('status', 'approved');

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['brand_id'])) {
            $query->where('brand_id', $filters['brand_id']);
        }

        if (!empty($filters['keyword'])) {
            $query->where('name', 'like', '%' . $filters['keyword'] . '%');
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['min_players'])) {
            $query->where('min_players', '<=', $filters['min_players'])
                  ->where('max_players', '>=', $filters['min_players']);
        }

        $total = $query->count();
        $list = $query->order('created_at', 'desc')
            ->page($page, $limit)
            ->select();

        return [
            'total' => $total,
            'list' => $list,
        ];
    }

    public static function getDetail(int $id, ?int $userId = null): ?Script
    {
        $script = Script::with(['brand', 'category', 'images'])->find($id);

        if (!$script || !$script->isApproved()) {
            return null;
        }

        $script->incrementViews();

        return $script;
    }

    public static function create(array $data): Script
    {
        $scriptImages = $data['images'] ?? [];
        unset($data['images']);

        $script = Script::create($data);

        foreach ($scriptImages as $index => $url) {
            ScriptImage::create([
                'script_id' => $script->id,
                'url' => $url,
                'sort_order' => $index,
            ]);
        }

        return $script;
    }

    public static function update(int $id, array $data): bool
    {
        $script = Script::find($id);
        if (!$script) {
            return false;
        }

        $scriptImages = $data['images'] ?? [];
        unset($data['images']);

        $script->save($data);

        if (!empty($scriptImages)) {
            ScriptImage::where('script_id', $id)->delete();
            foreach ($scriptImages as $index => $url) {
                ScriptImage::create([
                    'script_id' => $script->id,
                    'url' => $url,
                    'sort_order' => $index,
                ]);
            }
        }

        return true;
    }

    public static function delete(int $id): bool
    {
        $script = Script::find($id);
        if (!$script) {
            return false;
        }

        return $script->delete();
    }

    public static function audit(int $id, string $status): bool
    {
        $script = Script::find($id);
        if (!$script) {
            return false;
        }

        return $script->save(['status' => $status]);
    }
}
