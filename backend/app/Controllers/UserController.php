<?php
namespace app\controller;

use app\model\User;
use app\model\UserInteraction;
use app\model\Script;
use app\model\Brand;
use app\services\JwtService;

class UserController extends BaseController
{
    public function login()
    {
        $code = $this->request->param('code');
        if (empty($code)) {
            return $this->error('Code is required');
        }

        $wechatUrl = "https://api.weixin.qq.com/sns/jscode2session?appid=" . config('wechat.appid') . "&secret=" . config('wechat.secret') . "&js_code={$code}&grant_type=authorization_code";

        try {
            $response = file_get_contents($wechatUrl);
            $result = json_decode($response, true);

            if (isset($result['errcode'])) {
                return $this->error('WeChat login failed: ' . $result['errmsg']);
            }

            $openid = $result['openid'];

            $user = User::where('openid', $openid)->find();
            if (!$user) {
                $user = User::create(['openid' => $openid]);
            }

            $token = JwtService::encode(['user_id' => $user->id]);

            return $this->success([
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'nickname' => $user->nickname,
                    'avatar' => $user->avatar,
                ],
            ]);
        } catch (\Exception $e) {
            return $this->error('Login failed: ' . $e->getMessage());
        }
    }

    public function profile()
    {
        $user = $this->request->user;
        if (!$user) {
            return $this->error('Unauthorized', 401);
        }

        return $this->success([
            'id' => $user->id,
            'openid' => $user->openid,
            'nickname' => $user->nickname,
            'avatar' => $user->avatar,
            'created_at' => $user->created_at,
        ]);
    }

    public function update()
    {
        $user = $this->request->user;
        if (!$user) {
            return $this->error('Unauthorized', 401);
        }

        $data = $this->request->only(['nickname', 'avatar']);
        $user->save($data);

        return $this->success(null, 'Updated successfully');
    }

    public function favorites()
    {
        $user = $this->request->user;
        if (!$user) {
            return $this->error('Unauthorized', 401);
        }

        $page = (int) $this->request->param('page', 1);
        $limit = (int) $this->request->param('limit', 20);

        $interactions = UserInteraction::with(['target'])
            ->where('user_id', $user->id)
            ->where('action_type', 'collect')
            ->where('target_type', 'script')
            ->page($page, $limit)
            ->select();

        $scripts = [];
        foreach ($interactions as $interaction) {
            $script = Script::with(['brand', 'images'])->find($interaction->target_id);
            if ($script) {
                $scripts[] = $script->toApiData();
            }
        }

        return $this->success(['list' => $scripts]);
    }

    public function follows()
    {
        $user = $this->request->user;
        if (!$user) {
            return $this->error('Unauthorized', 401);
        }

        $page = (int) $this->request->param('page', 1);
        $limit = (int) $this->request->param('limit', 20);

        $interactions = UserInteraction::where('user_id', $user->id)
            ->where('action_type', 'follow')
            ->where('target_type', 'brand')
            ->page($page, $limit)
            ->select();

        $brands = [];
        foreach ($interactions as $interaction) {
            $brand = Brand::find($interaction->target_id);
            if ($brand) {
                $brands[] = [
                    'id' => $brand->id,
                    'name' => $brand->name,
                    'logo' => $brand->logo,
                    'follower_count' => $brand->follower_count,
                ];
            }
        }

        return $this->success(['list' => $brands]);
    }
}
