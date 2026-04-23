<?php
namespace app\controller\Admin;

use app\model\HomeBanner;
use app\model\HomeAd;
use app\controller\BaseController;

class HomeContentController extends BaseController
{
    public function banners()
    {
        $banners = HomeBanner::order('sort_order', 'asc')->select();

        return $this->success(['list' => $banners]);
    }

    public function createBanner()
    {
        $image = $this->request->param('image');
        $link = $this->request->param('link', '');
        $sortOrder = (int) $this->request->param('sort_order', 0);

        if (empty($image)) {
            return $this->error('Image is required');
        }

        $banner = HomeBanner::create([
            'image' => $image,
            'link' => $link,
            'sort_order' => $sortOrder,
        ]);

        return $this->success($banner, 'Created successfully', 201);
    }

    public function updateBanner(int $id)
    {
        $banner = HomeBanner::find($id);
        if (!$banner) {
            return $this->error('Banner not found', 404);
        }

        $data = $this->request->only(['image', 'link', 'sort_order']);
        $banner->save($data);

        return $this->success(null, 'Updated successfully');
    }

    public function deleteBanner(int $id)
    {
        $banner = HomeBanner::find($id);
        if (!$banner) {
            return $this->error('Banner not found', 404);
        }

        $banner->delete();

        return $this->success(null, 'Deleted successfully');
    }

    public function ads()
    {
        $ads = HomeAd::order('sort_order', 'asc')->select();

        return $this->success(['list' => $ads]);
    }

    public function createAd()
    {
        $image = $this->request->param('image');
        $link = $this->request->param('link', '');
        $sortOrder = (int) $this->request->param('sort_order', 0);

        if (empty($image)) {
            return $this->error('Image is required');
        }

        $ad = HomeAd::create([
            'image' => $image,
            'link' => $link,
            'sort_order' => $sortOrder,
        ]);

        return $this->success($ad, 'Created successfully', 201);
    }

    public function updateAd(int $id)
    {
        $ad = HomeAd::find($id);
        if (!$ad) {
            return $this->error('Ad not found', 404);
        }

        $data = $this->request->only(['image', 'link', 'sort_order']);
        $ad->save($data);

        return $this->success(null, 'Updated successfully');
    }

    public function deleteAd(int $id)
    {
        $ad = HomeAd::find($id);
        if (!$ad) {
            return $this->error('Ad not found', 404);
        }

        $ad->delete();

        return $this->success(null, 'Deleted successfully');
    }
}
