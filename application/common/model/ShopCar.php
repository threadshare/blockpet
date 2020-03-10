<?php
namespace app\common\model;

use think\Db;
use think\Model;

class ShopCar extends Model
{
	protected $insert = ['create_time'];


	//添加购物车
	public function addCar($uid,$product_id,$num=1){
		$map['uid']=$uid;
		$map['product_id']=$product_id;
		$re=$this->where($map)->find();
		if($re){
			$res=$this->isUpdate(true)->where($map)->setInc('num',$num);
		}else{
			$data['uid']=$uid;
			$data['product_id']=$product_id;
			$data['num']=$num;
			$res=$this->isUpdate(false)->save($data);
		}
		return $res;
	}
	//结算时计算金额
	public function countCar($list){
//		$bonus_score=0;
//    	$sign_score=0;
//    	$rebonus_score=0;
    	$amount=0;
//    	$yj_money=0;
    	foreach ($list as $key => $value) {
//    		$type=Db::name('shop_product')->where(['id'=>$value['product_id']])->value('type');
    		$price=Db::name('shop_product')->where(['id'=>$value['product_id']])->value('price');
//    		$pv=Db::name('shop_product')->where(['id'=>$value['product_id']])->value('pv');
    		$amount=$amount+$price*$value['num'];
//            $yj_money=$yj_money+$pv*$value['num'];
//    		if($type==1){
//    			$bonus_score=$bonus_score+$price*$value['num'];
//    		}
//    		if($type==2){
//    			$sign_score=$sign_score+$price*$value['num'];
//    		}
//    		if($type==3){
//    			$rebonus_score=$rebonus_score+$price*$value['num'];
//    		}
    	}
//    	return ['yj_money'=>$yj_money,'amount'=>$amount,'bonus_score'=>$bonus_score,'sign_score'=>$sign_score,'rebonus_score'=>$rebonus_score];
        return $amount;
	}
	//删除购物车
	public function delCar($ids){
		$res=$this::destroy($ids);
		return $res;
	}

	/**
     * 创建时间
     * @return bool|string
     */
    protected function setCreateTimeAttr()
    {
        return time();
    }

    /**
     * 购物车结算奖励消费积分
     * @param $user_id 用户ID
     * @param $id   订单ID
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function shopIntegralCount($user_id,$id)
    {
        $productList = Db::name('shop_order_info')->where('order_id',$id)->select();
        $amount = 0;
        foreach ($productList as $pv) {
            //商品信息
            $pType = Db::name('shop_product')->where('id',$pv['product_id'])->value('type');
            $pCount = $pType == 1 ? $pv['num']*$pv['price']*5 : $pv['num']*$pv['price'];
            $amount+= $pCount;
        }
        //积分结算会员升级
        model('UserLevel')->upgrade($user_id,$amount);
    }
}