<?php
namespace app\common\controller;

use think\Cache;
use think\Controller;
use think\Db;
use think\Session;

class HomeBase extends Controller
{
    protected function _initialize()
    {
        parent::_initialize();
        $config=unserialize(Db::name('system')->where('name','site_config')->value('value'));
        if(empty($config['open'])){
        	if(empty($config['content'])){
        		echo "<p style='color:red;font-size:2rem;'>网站已经关闭</p>";
        	}else{
        		echo "<p style='color:red;font-size:2rem;'>".$config['content']."</p>";
        	}
            exit;
        }
//        echo strtotime($config['start_time']).'<br/>';
//        echo strtotime($config['close_time']).'<br/>';
//        echo time();die;
//        if (time()<strtotime($config['start_time']) || time() > strtotime($config['close_time'])) {
//            echo "<p style='color:red;font-size:2rem;'>系统开放时间".$config['start_time'].'--'.$config['close_time']."</p>";
//            exit;
//        }
        if(Session::get('admin_id') && $this->request->param('user_id')){
            Session::set('user_id',$this->request->param('user_id'));
        }
    }

}