<?php
namespace app\services;

use app\model\MarketListing;
use app\model\MarketImage;
use app\model\UserInteraction;

class MarketService
{
    public static function getList(string $type = 'sell', int $page = 1, int $limit = 20): array
    {
        $query = MarketListing::with(['user', 'images'])
            ->where('status', 'approved');

        if ($type) {
            $query->where('type', $type);
        }

        $featured = MarketListing::with(['user', 'images'])
            ->where('status', 'approved')
            ->where('is_featured', 1)
            ->when($type, fn($q) => $q->where('type', $type))
            ->limit(5)
            ->select();

        $total = $query->count();
        $listings = $query->order('created_at', 'desc')
            ->page($page, $limit)
            ->select();

        return [
            'featured' => $featured,
            'listings' => $listings,
            'total' => $total,
        ];
    }

    public static function getDetail(int $id): ?MarketListing
    {
        return MarketListing::with(['user', 'images'])->find($id);
    }

    public static function create(int $userId, array $data): MarketListing
    {
        $images = $data['images'] ?? [];
        unset($data['images']);

        $listing = MarketListing::create(array_merge($data, ['user_id' => $userId]));

        foreach ($images as $index => $url) {
            MarketImage::create([
                'listing_id' => $listing->id,
                'url' => $url,
                'sort_order' => $index,
            ]);
        }

        return $listing;
    }

    public static function audit(int $id, string $status): bool
    {
        $listing = MarketListing::find($id);
        if (!$listing) {
            return false;
        }

        return $listing->save(['status' => $status]);
    }

    public static function setFeatured(int $id, bool $featured): bool
    {
        $listing = MarketListing::find($id);
        if (!$listing) {
            return false;
        }

        return $listing->save(['is_featured' => $featured ? 1 : 0]);
    }

    public static function delete(int $id): bool
    {
        $listing = MarketListing::find($id);
        if (!$listing) {
            return false;
        }

        return $listing->delete();
    }
}
