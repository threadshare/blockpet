<?php
/*
'''''''''''''''''''''''''''''''''''''''''''''''''''''''''
源码来自九牛网分享 9nw.cc
''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
 */
namespace app\index\controller;

use app\common\controller\IndexBase;
use think\Db;

class Order extends IndexBase
{
    //我的订单
    public function index()
    {
        $status=$this->request->param('status');
        if($status){
            $map['status']=$status;
        }
        $map['uid']=$this->user_id;
    	$list=Db::name('shop_order')->where($map)->paginate(10);
    	$this->assign('list', $list);
    	$this->assign('page', $list->render());
        return $this->fetch();
    }

    //订单详情
    public function orderInfo($id)
    {
        $info=Db::name('shop_order')->where('id',$id)->find();
        $product=Db::name('shop_order_info')->where('order_id',$id)->select();
        foreach ($product as $key => $value) {
            $product[$key]['product_info']=Db::name('shop_product')->where('id',$value['product_id'])->find();
        }
        $this->assign('info', $info);
        $this->assign('product', $product);
        return $this->fetch();
    }
    //确认收货
    public function orderSh()
    {
        if($this->request->isPost()){
            $order_id=$this->request->param('order_id');
            $order_info=Db::name('shop_order')->where('id',$order_id)->find();
            $re=model('ShopOrder')->orderSh($order_info);
            if($re){
               $reFh =  model('ShopOrder')->orderFenh($order_info);
               if ($reFh){
                   $this->success('收货成功');
               }else{
                   $this->error('收货成功,奖金发放失败');
               }
            }else{
                $this->error('收货失败');
            }
        }
    }
    
}
