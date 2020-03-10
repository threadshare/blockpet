<?php
namespace app\common\controller;

use app\common\controller\HomeBase;
use think\Cache;
use think\Db;
use think\Session;

class ActionBase extends IndexBase
{
//    public $user_id;
//    public $user;
    protected function _initialize()
    {
        parent::_initialize();
        $user = $this->user;
        if ($user['status']==0) {
            $this->error('请先激活账号');
        }
    }

}