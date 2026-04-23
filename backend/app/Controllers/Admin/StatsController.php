<?php
namespace app\controller\Admin;

use app\services\StatsService;
use app\controller\BaseController;

class StatsController extends BaseController
{
    public function overview()
    {
        $stats = StatsService::getOverview();

        return $this->success($stats);
    }

    public function trend()
    {
        $type = $this->request->param('type', 'daily');
        $days = (int) $this->request->param('days', 7);

        $trend = StatsService::getTrend($type, $days);

        return $this->success($trend);
    }
}
