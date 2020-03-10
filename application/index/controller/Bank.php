<?php
/*
'''''''''''''''''''''''''''''''''''''''''''''''''''''''''
源码来自九牛网分享 9nw.cc
''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
 */
namespace app\index\controller;
use app\common\controller\IndexBase;
use think\Db;

class Bank extends IndexBase
{
    //银行卡列表
    public function index(){
        $list=Db::name('bank')->where('uid',$this->user_id)->select();
        if(session('returnurl')){
            $this->assign('returnurl',session('returnurl'));
        }else{
            $this->assign('returnurl',GetCurUrl());
        }
        $this->assign('list',$list);
        return $this->fetch();
    }
    //银行卡添加
    public function addBank(){
        if($this->request->isPost()){
            $data=$this->request->post();
            $data['uid']=$this->user_id;
            $data['create_time']=time();
            $info=Db::name('bank')->where('uid',$this->user_id)->find();
            if(!$info){
                $data['default']=1;
            }
            if(isset($data['default']) && $data['default']){
                Db::name('bank')->where('uid',$this->user_id)->setField('default',0);
            }
            $re=DB::name('Bank')->insert($data);
            if($re){
                $this->success('银行卡添加成功',url('index'));
            }else{
                $this->error('添加失败');
            }
        }
        return $this->fetch();
    }
    //设为默认
    public function setDefault(){
        $bank_id=$this->request->param('id');
        Db::name('bank')->where('uid',$this->user_id)->setField('default',0);
        $re=Db::name('bank')->where(['uid'=>$this->user_id,'id'=>$bank_id])->setField('default',1);
        if($re){
            $this->success('设置成功');
        }else{
            $this->error('设置失败');
        }
    }
    //银行卡删除
    public function delBank($id){
        $re=Db::name('Bank')->where('id',$id)->delete();
        if($re){
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
    }
}
