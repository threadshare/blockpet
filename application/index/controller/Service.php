<?php
/*
'''''''''''''''''''''''''''''''''''''''''''''''''''''''''
源码来自九牛网分享 9nw.cc
''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
 */
namespace app\index\controller;

use app\common\controller\IndexBase;
use think\Cache;
use think\Controller;
use think\Db;
use My\DataReturn;

class Service extends IndexBase
{
    public function index()
    {
        return view();
    }
    public function help_center()
    {
        $newslist = Db::name('news')->where('cate',3)->select();
        return view()->assign('newslist',$newslist);
    }
    public function call_center()
    {
        $url_code = Db::name('recharge_mode')->value('server_img');
        return view()->assign('url_code',$url_code);
    }

}
