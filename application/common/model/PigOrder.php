<?php
namespace app\common\model;

use think\Db;
use think\Model;

class PigOrder extends Model
{

//    public function pigLevel($money)
//    {
//        $pigLevel = $this->where('max_price','egt',$money)->order('max_price','asc')->find();
//        return $pigLevel;
//    }
    public function cancel($id)
    {
        $orderInfo = $this->find($id);
        $re = $this->where('id',$id)
            ->setField([
                'status'=>0,
                'uid' => $orderInfo['sell_id'],
                'sell_id' => 0
            ]);
        return $re ? true : false;
    }
    public function confirm($id)
    {
        $re =$this
            ->where('id',$id)
            ->setField(['status'=>3,'update_time'=>time()]);
        if($re){

            $orderInfo = $this->find($id);
            //把猪添加到买方
            $userPig = [];
            //$order
            $pigInfo = Db::name('task_config')->where('id',$orderInfo['pig_id'])->find();
            $userPig['uid'] = $orderInfo['uid'];
            //$userPig['order_id'] = $id;
            //$userPig['pig_id'] = $orderInfo['pig_id'];
            //$userPig['pig_name'] = $orderInfo['pig_name'];
            //$userPig['pig_no'] = create_trade_no();
            //$userPig['cycle'] = $pigInfo['cycle'];
            //$userPig['contract_revenue'] = $pigInfo['contract_revenue'];
            //$userPig['doge'] = $pigInfo['doge'];
            $userPig['status'] = 0;
            $userPig['from_id'] = $orderInfo['sell_id'];
            $userPig['price'] = $orderInfo['price'];
            $userPig['create_time'] = time();
            $userPig['end_time'] = time()+$pigInfo['cycle']*24*3600;
            Db::name('user_pigs')->where('order_id',$id)->update($userPig);
            //销毁原来的猪
//            $map = [];
//            $map['order_id'] = $id;
//            $map['uid'] = $orderInfo['sell_id'];
//            Db::name('user_pigs')->where($map)->setField('status',2);
            //奖励PIG
            moneyLog($orderInfo['uid'],0,'pig',$pigInfo['wia'],9,'买入奖励wia');

            //奖金记录
            addReward($orderInfo['uid'],0,'pig',$pigInfo['wia'],5,'交易奖励wia');
            return true;
        } else{
            //$this->error('操作失败');
            return false;
        }
    }
}