<?php
namespace app\services;

use app\model\Brand;
use app\model\UserInteraction;

class BrandService
{
    public static function getList(int $page = 1, int $limit = 20, ?string $status = null): array
    {
        $query = Brand::query();

        if ($status) {
            $query->where('status', $status);
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

    public static function getDetail(int $id, ?int $userId = null): ?Brand
    {
        $brand = Brand::with(['approvedScripts.images'])->find($id);

        if (!$brand) {
            return null;
        }

        $brand->incrementViews();

        if ($userId) {
            $brand->is_followed = UserInteraction::where('user_id', $userId)
                ->where('target_type', 'brand')
                ->where('target_id', $id)
                ->where('action_type', 'follow')
                ->find() !== null;
        }

        return $brand;
    }

    public static function create(array $data): Brand
    {
        return Brand::create($data);
    }

    public static function update(int $id, array $data): bool
    {
        $brand = Brand::find($id);
        if (!$brand) {
            return false;
        }

        return $brand->save($data);
    }

    public static function audit(int $id, string $status): bool
    {
        $brand = Brand::find($id);
        if (!$brand) {
            return false;
        }

        return $brand->save(['status' => $status]);
    }

    public static function follow(int $userId, int $brandId): bool
    {
        $existing = UserInteraction::where('user_id', $userId)
            ->where('target_type', 'brand')
            ->where('target_id', $brandId)
            ->where('action_type', 'follow')
            ->find();

        if ($existing) {
            return false;
        }

        UserInteraction::create([
            'user_id' => $userId,
            'target_type' => 'brand',
            'target_id' => $brandId,
            'action_type' => 'follow',
        ]);

        Brand::where('id', $brandId)->increment('follower_count');

        return true;
    }

    public static function unfollow(int $userId, int $brandId): bool
    {
        $deleted = UserInteraction::where('user_id', $userId)
            ->where('target_type', 'brand')
            ->where('target_id', $brandId)
            ->where('action_type', 'follow')
            ->delete();

        if ($deleted) {
            Brand::where('id', $brandId)->where('follower_count', '>', 0)->dec('follower_count');
        }

        return $deleted > 0;
    }
}
