<?php
/*
'''''''''''''''''''''''''''''''''''''''''''''''''''''''''
源码来自九牛网分享 9nw.cc
''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
 */
namespace app\common\model;

use think\Model;
use think\Db;

class Trade extends Model
{
    /**
     * 期股挂买
     * @param $buy_id 用户ID
     * @param $num  挂买数量
     * @param $price    挂买价格
     * @return bool
     */
    public function buyOrder($buy_id,$num)
    {
        // 启动事务
        Db::startTrans();
        try{
            //订单
            Db::name('trade_order')->insert(['buy_id'=>$buy_id,'num'=>$num,'create_time'=>time()]);

            //资产变动
            //moneyLog($buy_id,$buy_id,'fruit',-$num,6,'果实挂买');
            // 提交事务
            Db::commit();
            return true;
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return false;
        }
    }

    /**
     * 匹配买单
     * @param $user_id  用户ID
     * @param $trade_id 订单ID
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function sellConfirm($user_id,$trade_id)
    {
        $orderInfo = Db::name('trade_order')->where('id',$trade_id)->find();
        // 启动事务
        Db::startTrans();
        try{
            //订单状态修改
            Db::name('trade_order')->where('id',$trade_id)
                ->update([
                    'sell_id' => $user_id,
                    'status' => 1
                ]);

            //卖出方期股变动
            moneyLog($user_id,$orderInfo['buy_id'],'stock_option',-$orderInfo['num'],7,'期股卖出');
            //买入方期股变动
            moneyLog($orderInfo['buy_id'],$user_id,'stock_option',$orderInfo['num'],7,'买单被匹配');
            //可用余额变动
            moneyLog($user_id,$orderInfo['buy_id'],'dym_balance',$orderInfo['num']*$orderInfo['price'],7,'期股卖出');
            // 提交事务
            Db::commit();
            return true;
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return false;
        }
    }

    /**
     * 挂卖单
     * @param $sell_id
     * @param $num
     * @param $price
     * @return bool
     */
    public function sellOrder($sell_id,$num,$price)
    {
        // 启动事务
        Db::startTrans();
        try{
            //订单
            Db::name('trade_order')->insert(['sell_id'=>$sell_id,'price'=>$price,'num'=>$num,'create_time'=>time()]);

            //资产变动
            moneyLog($sell_id,$sell_id,'stock_option',-$num,8,'期股挂卖');
            // 提交事务
            Db::commit();
            return true;
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return false;
        }
    }

    public function buyConfirm($user_id,$trade_id)
    {
        $orderInfo = Db::name('trade_order')->where('id',$trade_id)->find();
        // 启动事务
        Db::startTrans();
        try{
            //订单状态修改
            Db::name('trade_order')->where('id',$trade_id)
                ->update([
                    'buy_id' => $user_id,
                    'status' => 1
                ]);

            //期股变动
            moneyLog($user_id,$orderInfo['sell_id'],'stock_option',$orderInfo['num'],9,'期股买入');

            //买入方可用余额变动
            moneyLog($user_id,$orderInfo['sell_id'],'dym_balance',-$orderInfo['num']*$orderInfo['price'],9,'期股买入');
            //卖出方可用余额变动
            moneyLog($orderInfo['sell_id'],$user_id,'dym_balance',$orderInfo['num']*$orderInfo['price'],9,'卖单被匹配');
            // 提交事务
            Db::commit();
            return true;
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return false;
        }
    }

    /**
     * 取消订单
     * @param $order_id 订单ID
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function orderCancel($order_id)
    {
        $res = Db::name('trade_order')->where('id',$order_id)->delete();
        if ($res) {
            return true;
        } else {
            return false;
        }
    }

}