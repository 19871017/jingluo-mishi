<?php
namespace app\controller;

use think\App;
use think\Controller;

class BaseController extends Controller
{
    public function __construct(App $app)
    {
        parent::__construct($app);
    }

    protected function success($data = null, string $msg = 'Success', int $code = 200)
    {
        return json([
            'code' => $code,
            'msg' => $msg,
            'data' => $data,
        ]);
    }

    protected function error(string $msg = 'Error', int $code = 400, $data = null)
    {
        return json([
            'code' => $code,
            'msg' => $msg,
            'data' => $data,
        ]);
    }

    protected function paginate($paginate, $msg = 'Success')
    {
        return json([
            'code' => 200,
            'msg' => $msg,
            'data' => [
                'total' => $paginate['total'],
                'list' => $paginate['list'],
            ],
        ]);
    }
}
