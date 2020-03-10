<?php
/*
'''''''''''''''''''''''''''''''''''''''''''''''''''''''''
author:ming    contactQQ:811627583
''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
 */
namespace app\admin\controller;

use app\common\controller\AdminBase;
use think\Config;
use think\Db;
use think\Session;
/**
 * 管理员管理
 * Class AdminUser
 * @package app\admin\controller
 */
class Shop extends AdminBase
{
    protected function _initialize()
    {
        parent::_initialize();
    }



    /**
     * 商品列表
     * @return mixed
     */
    public function index()
    {
        $keyword=$this->request->param('keyword');
        $status=$this->request->param('status');
        $type=$this->request->param('type');
        if($keyword){
            $map['title']=['like',"%{$keyword}%"];
        }
        if(strlen($status)){
            $map['status']=$status;
        }else{
            $map['status']=['egt',0];
        }
        if(!empty($type)){
            $map['type']=$type;
        }

        $list=Db::name('shop_product')->where($map)->paginate(12,false,['query'=>$this->request->param()]);
        $this->assign('list',$list);
        $this->assign('page',$list->render());

        return $this->fetch();
    }

    /**
     * 编辑商品
     * @return mixed
     */
    public function editProduct()
    {
        $id=$this->request->param('id')?$this->request->param('id'):0;
        if($this->request->isPost()){
            $data=$this->request->post();
            //dump($data);die;
            if($id){
                $re=model('ShopProduct')->allowField(true)->save($data,['id'=>$id]);
            }else{
                $re=model('ShopProduct')->allowField(true)->save($data);
            }
            if($re!==false){
                $this->success('操作成功',url('index'));
                exit;
            }else{
                $this->error('操作失败');
            }
        }
        $cateList = Db::name('shop_type')->where('status',1)->field('id,uname,status')->select();
        $info=Db::name('shop_product')->where('id',$id)->find();
        $this->assign('info',$info);
        $this->assign('cateList',$cateList);
        return $this->fetch();
    }
    /**
     * 设置商品状态
     * @return mixed
     */
    public function setStatus()
    {
        $status=$this->request->param('status');
        $ids=$this->request->param('ids/a');
        $id=$this->request->param('id');
        if($ids){
            $map['id']=['in',$ids];
        }elseif($id){
            $map['id']=$id;
        }else{
            $map['id']=0;
        }
        if($status!==null){
            $re=Db::name('shop_product')->where($map)->setField('status',$status);
            $this->success('设置成功');
        }else{
            $this->error('错误的状态');
        }
    }
    /**
     * 删除商品
     * @param int   $id
     * @param array $ids
     */
    public function delProduct($id = 0, $ids = [])
    {
        $id = $ids ? $ids : $id;
        if ($id) {
            if (model('ShopProduct')->destroy($id)) {
                $this->success('删除成功');
            } else {
                $this->error('删除失败');
            }
        } else {
            $this->error('请选择需要删除的商品');
        }
    }

    /**
     * 商品分类列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function cate()
    {
        $keyword=$this->request->param('keyword');
        $status=$this->request->param('status');
        if($keyword){
            $map['uname']=['like',"%{$keyword}%"];
        }
        if(strlen($status)){
            $map['status']=$status;
        }else{
            $map['status']=['egt',0];
        }

        $list=Db::name('shop_type')->where($map)->paginate(12,false,['query'=>$this->request->param()]);
        $this->assign('list',$list);
        $this->assign('page',$list->render());
        return $this->fetch();

    }

    /**
     * 编辑商品分类
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function editCate()
    {
        $id=$this->request->param('id')?$this->request->param('id'):0;
        if($this->request->isPost()){
            $data=$this->request->post();
            if($id){
                $re=model('ShopType')->allowField(true)->save($data,['id'=>$id]);
            }else{
                $re=model('ShopType')->allowField(true)->save($data);
            }
            if($re!==false){
                $this->success('操作成功',url('cate'));
                exit;
            }else{
                $this->error('操作失败');
            }
        }
        $info=Db::name('shop_type')->where('id',$id)->find();
        $this->assign('info',$info);
        return $this->fetch();
    }
    public function delCate()
    {
        $id = $this->request->param('id');
        //查询分类下是否有商品
        $productList = Db::name('shop_product')->where('cate',$id)->find();
        $productList ? $this->error('该分类下有商品不能删除') : '';
        $res = Db::name('shop_type')->where('id',$id)->delete();
        $res ? $this->success('删除成功！') : $this->error('操作失败！');
    }
    /**
     * 订单列表
     * @return mixed
     */
    public function order()
    {
        $keyword=$this->request->param('keyword');
        $status=$this->request->param('status');
        $type=$this->request->param('type');
        if($this->request->param('keyword')){
            $map['contect_name|contect_tel']=$keyword;
        }
        if($status){
            $map['status']=$status;
        }else{
            $map['status']=['egt',0];
        }
        if($type){
            $map['type']=$type;
        }
        $list=Db::name('shop_order')->where($map)->paginate(12,false,['query'=>$this->request->param()]);
        $this->assign('list',$list);
        $this->assign('page',$list->render());
        return $this->fetch();
    }
    /**
     * 订单收货
     * @return mixed
     */
    public function orderSh()
    {
        $order_id=$this->request->param('order_id');
        $order_info=Db::name('shop_order')->where('id',$order_id)->find();
        $re=model('ShopOrder')->orderSh($order_info);
        if($re){
            $this->success('收货成功');
        }else{
            $this->error('收货失败');
        }
    }
    /**
     * 订单详情
     * @return mixed
     */
    public function orderInfo()
    {
        $id=$this->request->param('id');
        $map['id']=$id;
        $info=Db::name('shop_order')->where($map)->find();
        $order_info=Db::name('shop_order_info')->where('order_id',$id)->select();
        foreach ($order_info as $key => $value) {
            $order_info[$key]['product_info']=Db::name('shop_product')->where('id',$value['product_id'])->find();
        }
        if($this->request->isPost()){
            if($info['status']==3){
                $this->error('已经发货不能重复发货');
            }
            $data=$this->request->post();
            $re=model('ShopOrder')->orderFh($info['id'],$data['kd_name'],$data['kd_num']);
            if($re){
                $this->success('发货成功');
                exit;
            }else{
                $this->error('发货失败');
            }
        }
        $this->assign('info',$info);
        $this->assign('order_info',$order_info);
        return $this->fetch();
    }
    /**
     * 提现列表
     * @return mixed
     */
    public function withdraw()
    {
        $status=$this->request->param('status');
        $type=$this->request->param('type');
        if($status){
            $map['state']=$status;
        }else{
            $map['state']=['egt',0];
        }
        if($type){
            $map['type']=$type;
        }
        $list=Db::name('tixian')->where($map)->paginate(12,false,['query'=>$this->request->param()]);
        $this->assign('list',$list);
        $this->assign('page',$list->render());
        return $this->fetch();
    }
    /**
     * 提现处理
     * @return mixed
     */
    public function setWithdraw($id)
    {
        $data['examine_time']=time();
        $data['state']=1;
        //$data['msg']='处理成功';
        $re=Db::name('tixian')->where('id',$id)->update($data);
        if($re){
            $this->success('处理成功');
        }else{
            $this->error('处理失败');
        }
    }

    public function zcConfig()
    {
        $info = Db::name('zc_config')->find();
        if ($this->request->isPost()) {
            $data = $this->request->post();
            if(!empty($info)) {
                $res = Db::name('zc_config')->where('id',$info['id'])->update($data);
            } else {
                $res = Db::name('zc_config')->insert($data);
            }

            $res ? $this->success('操作成功') : $this->error('操作失败');
        }
        return view()->assign('info',$info);
    }
}