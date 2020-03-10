<?php
namespace app\common\model;

use think\Db;
use think\Model;
use think\Log;

class Pig extends Model
{
    protected $table = 'wym_task_config';
    public function pigLevel($money)
    {
        $pigLevel = $this->where('max_price','egt',$money)->order('max_price','asc')->find();
        if(!$pigLevel){
            $baseConfig = unserialize(Db::name('system')->where('name','base_config')->value('value'));
            $num = $baseConfig['split_num'];
            if($num < 2){
                $pigLevel = $this->where('max_price','egt',0)->order('max_price','desc')->find();
            }
        }
        return $pigLevel;
    }
    public function pigUpgarde($id,$money)
    {
        Db::name('user_pigs')->where('id',$id)->setInc('price',$money);
        //总收益
        Db::name('user_pigs')->where('id',$id)->setInc('total_revenue',$money);
        //用户累计收益
        $userPig = Db::name('user_pigs')->where('id',$id)->find();
        $pigLevel = $this->pigLevel($userPig['price']);

        if ($pigLevel) {
            echo '升级';
            Log::record('[ pigUpgarde ] '.$userPig['pig_no'].'升级', 'info');
            dump($pigLevel);
            if (time()+86400 >$userPig['end_time']) {
                Log::record('[ pigUpgarde ] '.$userPig['pig_no'].'挂卖到交易市场', 'info');
                //挂卖到交易市场
                $newOrderId = Db::name('PigOrder')->insertGetId([
                    'uid' => $userPig['uid'],
                    'pig_id' => $pigLevel['id'],
                    'sell_id' => 0,
                    'price' => $userPig['price'],
                    'create_time' => time(),
                    'status'=>0,
                    'pig_name'=>$pigLevel['name'],
                    'order_no'=> create_trade_no()
                ]);
                //更改可出售状态
                Db::name('user_pigs')
                    ->where('id',$id)
                    ->setField([
                        'status'=>1,
                        'order_id'=>$newOrderId,
                        'end_time'=>time()
                    ]);
            }
            if ($pigLevel['id'] != $userPig['pig_id']) {
                Db::name('user_pigs')
                    ->where('id',$id)
                    ->update([
                    'pig_id'=>$pigLevel['id'],
                    'pig_name' => $pigLevel['name'],
                    'from_id'=>0,
                    'status'=>1,
                    'end_time' => null,
                    'cycle'=>$pigLevel['cycle'],
                    'contract_revenue'=> $pigLevel['contract_revenue'],
                    'doge'=>$pigLevel['doge']
                ]);

            }

        } else {
            //拆分
            Log::record('[ pigUpgarde ] '.$userPig['pig_no'].'销毁', 'info');
            $baseConfig = unserialize(Db::name('system')->where('name','base_config')->value('value'));
            $num = $baseConfig['split_num'];
            if($num > 1){
                $sonMoney = $userPig['price']/$num;
                $newPig = $this->pigLevel($sonMoney);

                //改变状态为销毁
                Db::name('user_pigs')->where('id',$id)->setField('status',2);
                echo '销毁';
                dump($newPig);
                for ($i=1;$i<=$num;$i++) {

                    Db::name('user_pigs')->insert([
                       'uid' => $userPig['uid'],
                       'pig_id'=>$newPig['id'],
                        'pig_name'=>$newPig['name'],
                        'pig_no'=>create_trade_no(),
                        'price'=>$sonMoney,
                        'status'=> 1,
                        'from_id'=>0,
                        'cycle'=>$newPig['cycle'],
                        'contract_revenue'=>$newPig['contract_revenue'],
                        'doge'=>$newPig['doge'],
                        'create_time'=>time(),

                    ]);
                }
                //进销毁记录表
                Db::name('destory_pigs')->insert([
                   'uid'=>$userPig['uid'],
                    'pig_id'=>$userPig['id'],
                    'pig_level'=>$userPig['pig_name'],
                    'price'=>$userPig['price'],
                    'status'=>$userPig['status'],
                    'delete_time'=>time()
                ]);
                //删除原宠物
                Db::name('user_pigs')->where('id',$id)->delete();
            }
        }



    }
}