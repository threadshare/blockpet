<?php
namespace app\common\model;

use think\Db;
use think\Model;
use think\Log;
class ShopOrder extends Model
{
	protected $insert = ['create_time','order_num'];
    //获取月初时间戳
    //protected $month=mktime(0,0,0,date('m'),1,date('Y'));
    //获取年初时间戳
    //protected $year=strtotime(date("Y",time())."-1"."-1");
    
    //订单发货
    public function orderFh($order_id,$kd_name,$kd_num){
        $data['kd_name']=$kd_name;
        $data['kd_num']=$kd_num;
        $data['status']=2;
        $data['delivery_time'] = time();
        $re=Db::name('shop_order')->where('id',$order_id)->update($data);
        return $re;
    }

    //订单收货
    public function orderSh($order_info){
        $data['status']=3;
        $data['over_time']=time();
        $re=Db::name('shop_order')->where('id',$order_info['id'])->update($data);
//        $yj_money=$order_info['yj_money'];
//        if($re){
//            $this->updateYjCount($order_info,$yj_money);
//            $this->updateYj($order_info,$yj_money);
//            $this->sfsj($order_info);
//            $this->gpJd($order_info,$yj_money);
//            $this->cbFh($order_info,$yj_money);
//        }
        return $re;
    }
    //更新业绩更新市场达标次数
    public function updateYj($order_info,$yj_money){
        $config=unserialize(Db::name('system')->where('name','base_config')->value('value'));
        $user=Db::name('user')->where('id',$order_info['uid'])->find();
        //更新本人消费业绩
        Db::name('user')->where('id',$order_info['uid'])->setInc('yj_money',$yj_money);
        //更新本人本月是否达标 更新达标次数
        $map['total_money']=['egt',$config['scdb_js']];
        $map['uid']=$order_info['uid'];
        $scdb=Db::name('yj_count')->where($map)->value('id');
        if($scdb && !$user['scdb']){
            $data['scdb']=1;
            $data['scdb_count']=$user['scdb_count']+1;
            Db::name('user')->where('id',$order_info['uid'])->update($data);
        }
        //更新上级达标 以及达标次数
        $prel=str2arr($user['prel']);
        foreach ($prel as $key => $value) {
            $map['total_money']=['egt',$config['scdb_js']];
            $map['uid']=$value;
            $scdb=Db::name('yj_count')->where($map)->value('id');
            $scdb_re=Db::name('user')->where(['id'=>$value])->value('scdb');
            if($scdb && !$scdb_re){
                $data['scdb']=1;
                $data['scdb_count']=$user['scdb_count']+1;
                Db::name('user')->where('id',$value)->update($data);
            }
        }
        return true;
    }
    //更新业绩统计表
    public function updateYjCount($order_info,$yj_money){
        //获取月初时间戳
        $month=mktime(0,0,0,date('m'),1,date('Y'));
        $map['create_time']=['gt',$month];
        $map['uid']=$order_info['uid'];
        $yj_count=Db::name('yj_count')->where($map)->find();
        $data['uid']=$order_info['uid'];
        $data['create_time']=time();        
        if($yj_count){
            $data['yj_money']=$yj_count['yj_money']+$yj_money;
            $data['total_money']=$yj_count['total_money']+$yj_money;
            Db::name('yj_count')->where($map)->update($data);
        }else{
            $data['yj_money']=$yj_money;
            $data['team_money']=model('user')->getTeamYj($order_info['uid']);
            $data['total_money']=$data['yj_money']+$data['team_money'];
            Db::name('yj_count')->where($map)->insert($data);
        }
        $prel=str2arr(Db::name('user')->where('id',$order_info['uid'])->value('prel'));
        foreach ($prel as $key => $value) {
            if($value){
                $re=Db::name('yj_count')->where('uid',$value)->find();
                $re_data['team_money']=$re['team_money']+$yj_money;
                $re_data['total_money']=$re['total_money']+$yj_money;
                Db::name('yj_count')->where('uid',$value)->update($re_data);
                if(!$re){
                    Db::name('yj_count')
                    ->where('uid',$value)
                    ->insert(['uid'=>$value,'team_money'=>$yj_money,'total_money'=>$yj_money,'create_time'=>time()]);
                }
            }
        }
        return true;
    }
    //公排见点奖
    public function gpJd($order_info,$yj_money){
        $config=unserialize(Db::name('system')->where('name','base_config')->value('value'));
        $re_money=$yj_money;
        $jd_money=$re_money*$config['jdj']/100;
        $grel=str2arr(Db::name('user')->where('id',$order_info['uid'])->value('grel'));
        $money['glf']=$jd_money*$config['glf']/100;
        $money['rebonus_score']=$jd_money*$config['cx']/100;
        $money['bonus_score']=$jd_money-$money['glf']-$money['rebonus_score'];
        foreach ($grel as $key => $value) {
            if($key>=0 && $key%2==0){
                wealth($value,$order_info['uid'],$money,"公排见点奖");
            }
        }
        return true; 
    }

    //身份升级
    public function sfsj($order_info){
        $prel=str2arr(Db::name('user')->where(['id'=>$order_info['uid']])->value('prel'));
        foreach ($prel as $key => $value) {
            if($value){
                $map1['scdb_count']=['egt',1];
                $map1['pid']=$value;
                $count1=Db::name('user')->where($map1)->count();
                $map2['scdb_count']=['egt',3];
                $map2['pid']=$value;
                $count2=Db::name('user')->where($map2)->count();
                $identity=Db::name('user')->where('id',$value)->value('identity');
                $child_ids=Db::name('user')->where('pid',$value)->column('id');

                $i=0;
                foreach ($child_ids as $k => $v) {
                    if($i>=2) break;
                    $re_map['prel']=['like',"%,".$v.",%"];
                    $re_map['identity']=['gt',$identity];
                    $re_count=Db::name('user')->where($re_map)->count();
                    $re_identity=Db::name('user')->where('id',$v)->value('identity');
                    if($re_count>=1 || $re_identity>$identity){
                        $i++;
                    }
                }
                if($count1>=2){
                    Db::name('user')->where('id',$value)->update(['identity'=>1]);
                }
                if($count2>=3){
                    Db::name('user')->where('id',$value)->update(['identity'=>2]);
                }
                if($count2>=6){
                    Db::name('user')->where('id',$value)->update(['identity'=>3]);
                }
                $re=Db::name('user')->where('id',$value)->value('identity');
                if($i>=2 && $re<=$identity+1){
                    Db::name('user')->where('id',$value)->update(['identity'=>$identity+1]);
                }
            }
        }
        return true; 
    }
    //筹备返还奖新增业绩返还
    public function cbFh($order_info,$yj_money){
        $config=unserialize(Db::name('system')->where('name','base_config')->value('value'));
        $prel=str2arr(Db::name('user')->where(['id'=>$order_info['uid']])->value('prel'));
        foreach ($prel as $key => $value) {
            if($value){
                $map['mark']=["in","团队业绩奖,市场达标奖,利润返还,新增业绩奖"];
                $map['uid']=$value;
                $gain=Db::name('wealth')->where($map)->sum('money');
                $money=0;
                $total=-1;
                $user=Db::name('user')->where('id',$value)->find();
                if($user['yj_money']>=$config['cbfh_amount'][2]){
                    $total=$user['yj_money']*$config['cbfh_times'][2];
                    $money=$config['cbfh_scale'][2]*$yj_money/100;
                }elseif($user['yj_money']>=$config['cbfh_amount'][1]){
                    $total=$user['yj_money']*$config['cbfh_times'][1];
                    $money=$config['cbfh_scale'][1]*$yj_money/100;
                }elseif($user['yj_money']>=$config['cbfh_amount'][0]){
                    $total=$user['yj_money']*$config['cbfh_times'][0];
                    $money=$config['cbfh_scale'][0]*$yj_money/100;
                }
                if($total<=$gain){
                    continue;
                }
                $re_arr['rebonus_score']=$money*$config['cx']/100;
                $re_arr['glf']=$money*$config['glf']/100;
                $re_arr['money']=$money-$re_arr['rebonus_score']-$re_arr['glf'];
                wealth($value,$order_info['uid'],$re_arr,"新增业绩奖");
            }
        }
        return true; 
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
     * 创建订单编号
     * @return bool|string
     */
    protected function setOrderNumAttr()
    {
        return build_order_no();
    }
}