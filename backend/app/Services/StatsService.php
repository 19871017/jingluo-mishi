<?php
namespace app\services;

use app\model\Script;
use app\model\Brand;
use app\model\Category;
use app\model\User;
use app\model\MarketListing;

class StatsService
{
    public static function getOverview(): array
    {
        return [
            'total_scripts' => Script::where('status', 'approved')->count(),
            'total_brands' => Brand::where('status', 'approved')->count(),
            'total_users' => User::count(),
            'total_market_listings' => MarketListing::where('status', 'approved')->count(),
            'pending_scripts' => Script::where('status', 'pending')->count(),
            'pending_brands' => Brand::where('status', 'pending')->count(),
            'pending_market_listings' => MarketListing::where('status', 'pending')->count(),
        ];
    }

    public static function getTrend(string $type = 'daily', int $days = 7): array
    {
        $format = $type === 'daily' ? '%Y-%m-%d' : '%Y-%m';
        $dateField = $type === 'daily' ? 'created_at' : 'created_at';

        $scripts = Script::whereTime('created_at', '-', $days . ' days')
            ->field("DATE_FORMAT(created_at, '{$format}') as date, COUNT(*) as count")
            ->group("DATE_FORMAT(created_at, '{$format}')")
            ->select();

        $brands = Brand::whereTime('created_at', '-', $days . ' days')
            ->field("DATE_FORMAT(created_at, '{$format}') as date, COUNT(*) as count")
            ->group("DATE_FORMAT(created_at, '{$format}')")
            ->select();

        return [
            'scripts' => $scripts,
            'brands' => $brands,
        ];
    }
}
