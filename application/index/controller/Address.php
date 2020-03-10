<?php
/*
'''''''''''''''''''''''''''''''''''''''''''''''''''''''''
源码来自九牛网分享 9nw.cc
''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
 */
namespace app\index\controller;
use app\common\controller\IndexBase;
use think\Db;

class Address extends IndexBase
{
    //地址列表
    public function index(){
        $list=Db::name('Address')->where('uid',$this->user_id)->select();
        if(session('returnurl')){
            $this->assign('returnurl',session('returnurl'));
        }else{
            $this->assign('returnurl',GetCurUrl());
        }
        $this->assign('list',$list);
        return $this->fetch();
    }
    //地址编辑
    public function editAddress(){
        if($this->request->isPost()){
            $data=$this->request->post();
            $data['uid']=$this->user_id;
            $info=Db::name('Address')->where('uid',$this->user_id)->find();
            if(!$info){
                $data['default']=1;
            }
            // if(!check_mobile_number($data['contect_tel'])){
            //     $this->error('请输入正确的手机号');
            // }
            if(isset($data['default']) && $data['default']){
                Db::name('Address')->where('uid',$this->user_id)->setField('default',0);
            }
            if($data['id']){
                $re=Db::name('Address')->where('id',$data['id'])->update($data);
            }else{
                $re=Db::name('Address')->insert($data);
            }           
            if($re!==false){
                $this->success('地址添加成功',url('index'));
            }else{
                $this->error('添加失败');
            }
        }
        $id=$this->request->param('id');
        $find = Db::name('Address')->where(array('id'=>$id))->find();
        $province =  Db::name('Area')->where(array('pid'=>0))->order('sort')->select();
        $city = Db::name('Area')->where(array('pid'=>$find['province']))->order('sort')->select();
        $area = Db::name('Area')->where(array('pid'=>$find['city']))->order('sort')->select();
        $this->assign('info', $find);
        $this->assign('province', $province);
        $this->assign('city', $city);
        $this->assign('area', $area);
        return $this->fetch();
    }
    //设为默认
    public function setDefault($id){
        Db::name('Address')->where('uid',$this->user_id)->setField('default',0);
        $re=Db::name('Address')->where(['uid'=>$this->user_id,'id'=>$id])->setField('default',1);
        if($re){
            $this->success('设置成功');
        }else{
            $this->error('设置失败');
        }
    }
    //银行卡删除
    public function delAddress($id){
        $re=Db::name('Address')->where('id',$id)->delete();
        if($re){
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
    }

    /*
     *省市区三级联动
    */
    public function linkage(){
        if ($this->request->isPost()) {
          $id = $this->request->post('id');
          $list = Db::name('Area')->where(array('pid'=>$id))->select();
          return $list;
          exit;
        }
        exit;
    }
}
