<?php
/*
'''''''''''''''''''''''''''''''''''''''''''''''''''''''''
源码来自九牛网分享 9nw.cc
''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
 */
namespace app\index\controller;

use app\common\controller\IndexBase;
use think\Db;

class News extends IndexBase
{
    public function index()
    {
    	$list=model('Article')->where(['cid'=>1,'status'=>1])->paginate(10);
    	$this->assign('list', $list);
    	$this->assign('page', $list->render());
        return $this->fetch();
    }
    public function news_detail()
    {
    	$id=$this->request->param('id');
    	$info=model('Article')->where(['id'=>$id,'status'=>1])->find();
    	if(!$info){
    		$this->error('文章ID不正确');
    	}
    	$this->assign('info',$info);
        return $this->fetch();
    }
    //公告详情
    public function info($id){
        $info=Db::name('article')->where(['id'=>$id,'status'=>1])->find();
        $this->assign('info',$info);
        return $this->fetch();
    }
}
