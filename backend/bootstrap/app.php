<?php
namespace app;

use think\App;
use think\Container;

Container::get('app')->bind([
    'index' => \app\controller\Index::class,
]);
