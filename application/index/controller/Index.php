<?php
/*
'''''''''''''''''''''''''''''''''''''''''''''''''''''''''
源码来自九牛网分享 9nw.cc
''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
 */
namespace app\index\controller;

use app\common\controller\IndexBase;
use think\Cache;
use think\Controller;
use think\Db;
use My\DataReturn;

class Index extends IndexBase
{
    //首页
    public function index()
    {
        $piglist = Db::name('task_config')->select();
        $nowtime = date('H:i');
        $nowday = date('Y-m-d ');
        $time = time();

        foreach ($piglist as $key=>$val) {
            //dump($val);
            //dump($nowtime<$val['start_time'] || $nowtime>$val['end_time']);
            if ($nowtime<$val['start_time'] || $nowtime>$val['end_time']) {
                //echo 'yuyue';
                if (!$this->isYuyue($val['id'])) {
                    $piglist[$key]['game_status'] =1; //预约
                } else {
                    $piglist[$key]['game_status'] =2; //已预约
                }

            }elseif ($nowtime>=$val['start_time']) {
              //  echo 'open';
                if ($val['is_open'] == 0 && $this->isYuyue($val['id'])) {
                    $piglist[$key]['game_status'] = 4;//开奖中
                }else if($nowtime<$val['end_time']){
                    $piglist[$key]['game_status'] = 4;//抢购
                }else{
                    $piglist[$key]['game_status'] = 4;//开奖中
                }
            } else {
            //echo '00';
                $piglist[$key]['game_status']=0;
            }
        }
        $config=unserialize(Db::name('system')->where('name','site_config')->value('value'));
        //dump($piglist);die;
        return view()->assign(['piglist'=>$piglist,'nowday'=>$nowday,'nowtime'=>$time,'config'=>$config]);
    }
    public function isYuyue($pig_id)
    {
        $map = [];
        $map['uid'] = $this->user_id;
        $map['pig_id'] = $pig_id;
        $map['status'] = 0;
        $insertData['buy_type'] = ['<>', 1];
        $res = Db::name('yuyue')->where($map)->find();
        return $res ? 1 : 0;
    }
    public function checkGame(){
        $game = model('Game');
        $time = $game->excute_time();
        $now_game_time = strtotime($game->gaming_model['start_time']);
        $now_end_time = strtotime($game->gaming_model['end_time']);
        //dump($game);
        //前端显示开奖剩余时间
        $plus_time = $game->excute_time() - $game->openaward;
        //id 游戏ID  time 游戏时间  openaward 开奖冷却时间
        DataReturn::returnJson(200,'',['id'=>$game->game_id,'end_time'=> $now_end_time,'openaward'=>$game->openaward,'cool_time'=>$game->getCoolTime() + 1,'start_time'=>$now_game_time,'stage'=>$game->gameTimeArea($now_game_time)]);

    }
    public function yuyue()
    {
        if (Db::name('user')->where('id',$this->user_id)->value('is_jihuo') != 1) {
            $this->error('请先激活账户');
        }

        $data = $this->request->param();
        //dump($data);
        $pig_id = $data['id'];
        $pigInfo =  Db::name('task_config')->where('id',$pig_id)->find();
        $nowTime = date('H:i');
        if ($nowTime>$pigInfo['start_time'] && $nowTime<$pigInfo['end_time'])
        {
            $this->error('不是预约时间');
        }
        //是否实名通过
        $authMap = [];
        $authMap['uid'] = $this->user_id;
        $authMap['status'] = 1;
        if (!Db::name('identity_auth')->where($authMap)->find()) $this->error('请先实名');

        $hasYuyue = $this->isYuyue($pig_id);
        if ($hasYuyue) $this->error('已预约');
        //微分

        if ($this->user['pay_points']<$pigInfo['pay_points']){
            $this->error('微分不足,请充值');
        }
        $insertData = [];

        $insertData['uid'] = $this->user_id;
        $insertData['pig_id'] = $pig_id;
        $insertData['create_time'] = time();
        $insertData['user_sort'] = $this->user['trade_order'];
        $insertData['credit_score'] = $this->user['credit_score'];
        $insertData['buy_type'] = 0;

        $insertData['pay_points'] = $pigInfo['pay_points'];
        $re = Db::name('yuyue')->insert($insertData);
        if ($re) {
            //减少微分
            moneyLog($this->user_id,0,'pay_points',-$pigInfo['pay_points'],3,'预约宠物');
            $this->success('预约成功');

        }else {
            $this->error('预约失败');
        }

    }
    public function yuyueStatus()
    {
        $id = $this->request->param('id');
        $map = [];
        $map['uid'] = $this->user_id;
        $map['pig_id'] = $id;
        $map['status'] = 0;
        $res = Db::name('yuyue')->where($map)->find();

        $code = $res ? 1 : 0;
        return json(['code'=>$code]);

    }
    public function checkOpen()
    {
       $id = $this->request->param('id');
//         dump(Cache::get('is_open'.$id));
        //dump($id);
        $is_open = Cache::get('is_open'.$id);
        //dump(Cache::clear());die;
        if (!$is_open) {
            return json(['code'=>0,'msg'=>'未开奖']);
        } else {
            $luckyUsers = Cache::get('buy_'.date('Ymd').$id);
            //dump($luckyUsers);die;

            $uid = $this->user_id;
            if (!empty($luckyUsers)) {
                if (in_array($uid,$luckyUsers)) {
                    return json(['code'=>1,'msg'=>'恭喜']);
                } else {
                    return json(['code'=>2,'msg'=>'很遗憾']);
                }
            }else{
                return json(['code'=>2,'msg'=>'很遗憾']);
            }

        }

    }


    public function flash_buy()
    {
        if (Db::name('user')->where('id',$this->user_id)->value('is_jihuo') != 1) {
            $this->error('请先激活账户');
        }
        $data = $this->request->param();
        //dump($data);
        $pig_id = $data['id'];
        $pigInfo =  Db::name('task_config')->where('id',$pig_id)->find();
        $nowTime = date('H:i');
        if ($nowTime<$pigInfo['start_time'] || $nowTime>$pigInfo['end_time'])
        {
            $this->error('不是开抢时间');
        }
        //是否实名通过
        $authMap = [];
        $authMap['uid'] = $this->user_id;
        $authMap['status'] = 1;
        if (!Db::name('identity_auth')->where($authMap)->find()) $this->error('请先实名');

        //微分
        if ($this->user['pay_points']<$pigInfo['qiang_points']){
            $this->error('微分不足,请充值');
        }

        $map = [];
        $map['uid'] = $this->user_id;
        $map['pig_id'] = $pig_id;
        $map['status'] = 0;
        $insertData['buy_type'] = 1;
        $res = Db::name('yuyue')->where($map)->find();

        if(!$res){
            $insertData = [];

            $insertData['uid'] = $this->user_id;
            $insertData['pig_id'] = $pig_id;
            $insertData['create_time'] = time();
            $insertData['user_sort'] = $this->user['trade_order'];
            $insertData['credit_score'] = $this->user['credit_score'];
            $insertData['buy_type'] = 1;

            $insertData['pay_points'] = $pigInfo['qiang_points'];
            $re = Db::name('yuyue')->insert($insertData);
            if ($re) {
                //减少微分
                moneyLog($this->user_id,0,'pay_points',-$pigInfo['qiang_points'],3,'抢购宠物');
                $this->success('进入抢购成功');

            }else {
                $this->error('抢购失败');
            }
        }else if($this->isYuyue($pig_id)){
            $map = [];
            $map['uid'] = $this->user_id;
            $map['pig_id'] = $pig_id;
            $map['status'] = 0;
            $insertData['buy_type'] = ['<>', 1];
            //已经预约的，修改bug_type为2
            $re = Db::name('yuyue')->insert($insertData);
            Db::name('yuyue')->where($map)->update(['buy_type'=>2]);
            $this->success('进入抢购成功');
        }else{
            $this->success('进入抢购成功');
        }
    }

    public function checkFlushOpen()
    {
        $id = $this->request->param('id');
        $endtime = $this->request->param('endtime');
        $uid = $this->user_id;
        $nowTime = date('H:i:s',time()-180);

        $is_open = Cache::get('is_open'.$id);
        if (!$is_open && $nowTime<=$endtime) {
            return json(['code'=>0,'msg'=>'未开奖']);
        } else {
            $luckyUsers = Cache::get('buy_'.date('Ymd'). $id);
            //dump($luckyUsers);die;

            if (!empty($luckyUsers)) {
                if (in_array($uid,$luckyUsers)) {
                    return json(['code'=>1,'msg'=>'恭喜']);
                } else {
                    return json(['code'=>2,'msg'=>'很遗憾']);
                }
            }else{
                return json(['code'=>2,'msg'=>'很遗憾']);
            }

        }

    }

}
