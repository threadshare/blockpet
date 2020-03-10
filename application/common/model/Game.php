<?php
/**
 * Created by PhpStorm.
 * User: Xgh
 * Date: 2018/12/10
 * Time: 9:18
 */

namespace app\common\model;
use app\api\controller\JuHe;
use redis\Redis;
use think\Db;

class Game
{
    public $game_id ;
    protected $daojishi; //倒计时
    public $openaward ; //开奖
    protected $now_time ; //现在的时间
    protected $next_level_time ; //下一个阶段的时间
    public $gaming_model ; //现在的游戏
    protected $all_game_model ; //所有游戏模型
    protected $redis ;
    protected $game_name_status_pre ; //游戏场次状态前缀
    protected $game_name_status_expire_time ; //过期时间
    protected $game_award_list ; //

    public function __construct()
    {
        $this->config();
        $this->game_name_pre = 'game_name_pre';
        $this->game_name_expire_time =7200;
        $this->game_award_list = 'game_award_list_';

    }

    //返回某一个游戏的阶段，下个阶段的时间
    public function getRuningInfo(){
        $game = $this->runing();
        $info = [];
        if(!!$game){
            $level = $this->gameTimeArea($game['start_time']);
            $info['next_time'] = $this->next_level_time;
            $info['level'] = $level;
            $info['id'] = $game['id'];
        }
        return $info;
    }

    public function getCoolTime(){
        return $this->daojishi;
    }

    //获取预约中的游戏
    public function runing(){
        //当前时间
        $now_time = date('H:i:s',time()-300);
        //$where['start_time'] = ['lt',$now_time];
        $where['is_open'] = 0;
        //$where['is_display'] = 1;
        $where['end_time'] = ['gt',$now_time];
        $pig_list = Db::name('task_config')->where($where)->order('start_time')->find();
        return $pig_list ;
    }

    //游戏配置
    public function config(){
        //$value = Db::name('config')->where('name','daojishi')->value('value');
        //$value = $value ? $value : 120;
        $value = 120;
        $this->daojishi = $value;
        $this->openaward = 60 * 5;
        $this->now_time = time();
    }

    //开奖时间
    public function getGameOpenTime($game_id){
        $start_time = Db::name('pig_goods')->where('id',$game_id)->value('start_time');

        return strtotime($start_time) + $this->openaward ;
    }

    //下个游戏执行预约时间
    public function excute_time(){
        $pig = $this->runing();
        //dump($pig);
        if(!!$pig){
            //echo '存在';
            $this->game_id = $pig['id'];

            $this->gaming_model = $pig;
            //改变游戏ID
            $start_time  =strtotime('Ymd')+ strtotime($pig['start_time']) + $this->openaward;
            return $start_time  - time();
        }else{
           //echo '不存在';
            $pig_list = Db::name('task_config')->order('start_time')->find();
            //dump($pig_list);
            $this->game_id = $pig_list['id'];
            $this->gaming_model = $pig_list;
            $end_time = strtotime(date('Ymd')) + strtotime(date($pig_list['start_time'])) +86400 ;
            return $end_time - time() + $this->openaward;
        }
    }


    //判断是否到了开奖时间
    //判断是否到了开奖时间
    public function openGame(){
        $_now_time = time();
        //游戏封闭区 凌晨0~2点不执行开奖 -2019年2月16日16:32:53
        if($this->timeStopOpenGame($_now_time)){
            $now_time = date('H:i:s',$_now_time - $this->openaward);
            $where['start_time'] = ['elt',$now_time];
            $where['today_is_open'] = 0;
            $where['is_display'] = 1;
            $where['is_lock'] = 0;
            $game = Db::name('pig_goods')->where($where)->order('start_time')->find();
            if(!empty($game)){
                //直接锁表
                Db::name('pig_goods')->where($where)->save(['is_lock'=>1]);

                $this->game_id = $game['id'];
                $this->gaming_model = $game;
                $this->flashBuy();
            }
        }

    }

    function timeStopOpenGame($time){
        //获取当前0时0分
        $today_0 = strtotime(date('Ymd',$time));
        $sub_time = $time - $today_0;
        if($sub_time > 7200){
            return true;
        }else{
            return false;
        }
    }


    //每个游戏的状态区间
    function gameTimeArea($game_time){
        $stage_1 = $game_time -  $this->daojishi;
        $stage_3 = $game_time +  $this->openaward;
        $now_time = $this->now_time;
        $_stage = 1;
        //$this->connection->send('区间开始');
        if($now_time < $stage_1){
            //倒计时之前
            //$this->stage1($stage_1,$id);
            $_stage = 1;
            $this->next_level_time = $stage_1 - $now_time;
            // return 1;
        }elseif($now_time >= $stage_1 && $now_time < $game_time){
            //$this->stage2($game_time);
            $_stage = 2;
            //倒计时中
            $this->next_level_time = $game_time - $now_time;

            //  return 2;
        }elseif($now_time<$stage_3 && $now_time >= $game_time ){
            //开奖中
            //$this->stage3();
            $_stage = 3;
            $this->next_level_time = $game_time - $now_time;

            //  return 3;
        }elseif($now_time > $stage_3){
             return 4;
        }
        return $_stage;
    }


    //设置所有游戏的模型
    public function setGameModel($data){
        $this->all_game_model = $data;
    }

    //将模型附加游戏阶段
    public function addGameLevel(){
        $model = $this->all_game_model;
        foreach($model as $k => $v){
            $_time =strtotime($v['start_time']);
            $model[$k]['game_level'] = $this->gameTimeArea($_time);
        }
        return $model;
    }

    //当天的游戏时间
    public function exchage_time($time){
    }


    public function setGameId($id){
        $this->game_id = $id;
    }
    //抢购
    /*
     * 优先给指定的人的猪
     *
     * */
    public function flashBuy(){
        $redis_name = $this->game_name_pre . $this->game_id;
        $this->getGameStatus();
        $game_status = Redis::get($redis_name);
        if($game_status == 1){
            $this->updateGameStatus(2);
            $pigbuy = new PigFlashBuy();
            $redis_users = [];
            $redis_users = $pigbuy->getUsers($this->game_id);
            Redis::set('users_list_queue_'.$this->game_id,$redis_users);
            //exec("cd /www/wwwroot/daxiongqukuaizhu ;chown -R www:www runtime;chmod 0755 -R runtime");
            exec("cd /www/wwwroot/zhugongmeng ;php think doflashbuy {$this->game_id} 0",$a,$b);
        }
    }

    public function updateOneData(){
        //$pig = 1;
        $pig = Db::name('user_exclusive_pig')->where('id',1)->find();
        $award_user_id = 24;

        $this->createOrder($pig,$award_user_id);
    }


    public function createOrder($pig,$award_user_id,$type = 2){
        try{
            //短信处理
            $sms = new JuHe();
            $data['sell_user_id'] = $pig['user_id'];
            $data['pig_level'] = $pig['pig_id'];
            $data['pig_price'] = $pig['price'];
            $data['pig_id'] = $pig['id'];
            //$data['user_id'] = $award_user;
            $data['establish_time'] = time();
            $data['end_time'] = time() + 3600 * 2;
            $data['pig_order_sn'] = $this->get_order_sn();
            $data['purchase_user_id'] = $award_user_id;
            $pig_order = Db::name('pig_order')->insertGetId($data);
            $pres = true;



            //这里加下盐
            $buy_type = 'createOrder';
            $res = new \app\api\controller\Addsalt();
            $pig_salt=$res->pigaddsalt($pig['user_id'],$pig_order,$pig['buy_time'],$buy_type);
            //将指定的去掉
            $save_user = Db::name('user_exclusive_pig')->where('id',$pig['id'])->where('is_able_sale',1)->save(['order_id'=>$pig_order,'pig_salt'=>$pig_salt,'buy_type'=>$buy_type,'appoint_user_id'=>0,'is_able_sale'=>0]);

            if(!$save_user)
                return ['status'=>0,'msg'=>'更新失败'];

            //将预约记录是否抢到改一下状态
            $p_res_id = Db::name('pig_reservation')->where('pig_id',$pig['pig_id'])->where('user_id',$award_user_id)->whereTime('reservation_time','today')->value('id');
            if(!empty($p_res_id)){
                $pres = Db::name('pig_reservation')->where('pig_id',$pig['pig_id'])->where('user_id',$award_user_id)->whereTime('reservation_time','today')->save(['reservation_status'=>1]);
            }

            if($type == 1){
                //抢购后，扣除对应的福分
                $game_model = Db::name('pig_goods')->where('id',$pig['pig_id'])->find();
                //判断是否已经预约了
                $is_yuyue = $this->isYuyue($award_user_id);
                $fufen_do = true;

                if(!$is_yuyue){

                    $desc = sprintf('抢购%s消耗积分',$game_model['goods_name']);
                    $adoptive_energy = $game_model['adoptive_energy'];
                    //$desc = '抢购消耗福分';
                    //扣除福分
                    $fufen_do = accountLog($award_user_id,0,-"{$adoptive_energy}",$desc,0,0,0,4,$pig['pig_id']);
                }
            }

            if($pig_order  && $save_user ){
                //发送短信，
                $purchase_mobile = Db::name('users')->where('user_id',$award_user_id)->value('mobile');
                $sell_mobile = Db::name('users')->where('user_id',$pig['user_id'])->value('mobile');
                $sms->sendJuHeSms(3,$purchase_mobile,1111);//抢购人
                $sms->sendJuHeSms(4,$sell_mobile,1111);//出售人


                if($award_user_id != $pig['user_id']){
                    $redis = new Redis();
                    $redis->sadd($this->game_award_list . $this->game_id,$award_user_id);
                }


                return ['status'=>1,'msg'=>'成功'];
            }else{
                return ['status'=>0,'msg'=>'程序数据更新出错'];
            }
        }catch(\Exception $e){

            trace($pig,'game');
            trace($award_user_id,'game');
            trace($e->getmessage(),'game');

            return ['status'=>0,'msg'=>'创建订单失败'];
        }

    }

    //判断是否已经预约了--依赖setGameId
    public function isYuyue($user_id){
        $rs = Db::name('pig_reservation')->whereTime('reservation_time','today')->where(['user_id' => $user_id])->where('pig_id',$this->game_id)->column('pig_id');
        return !empty($rs) ? true : false;
    }

    /**
     * 获取订单 order_sn
     * @return string
     */
    public function get_order_sn()
    {
        $order_sn = null;
        // 保证不会有重复订单号存在
        while(true){
            $order_sn = date('YmdHis').rand(1000,9999); // 订单编号
            $order_sn_count = Db::name('pig_order')->where("pig_order_sn = ".$order_sn)->count();
            if($order_sn_count == 0)
                break;
        }
        return $order_sn;
    }

    //写入开奖时间
    public static function writeTime($game_id = '',$time = ''){
        //$start_time = Db::name('pig_goods')->where('id',$game_id)->value('start_time');
        //$game = '-------------------------------------------------\n';
       // $game = sprintf('下一场游戏的ID:%s,开始时间是:%s',$game_id,$time).'\n';
        $game = 'youxi';
        //$file_name = 'open_game_'.date('d',time()).'.txt';
        //trace($game,'game');
        file_put_contents(ROOT_PATH .'/public/gamelog/opengame.txt',$game);
    }

    public function updateGameStatus($status){
        $game_name = $this->game_name_pre . $this->game_id;
        Redis::set($game_name,$status);
    }

    //redis 获取场次状态--依赖setGameId
    public function getGameStatus(){
        $game_name = $this->game_name_pre . $this->game_id;
        return Redis::setnx($game_name,1,$this->game_name_expire_time);
    /*    $game_name = $this->game_name_pre . $this->game_id;
        $game = Redis::get($game_name);
        if($game){
            $json_game = json_decode($game);
            return $json_game['status'];
        }else{
            $game = Db::name('pig_goods')->
            $data['status'] = 0;//设置默认值
            $data['game_time'] = ;//设置游戏时间
            Redis::set($game_name,$status, $this->game_name_expire_time);
            return $status;
        }*/
    }


    /**
    *   开奖补偿机制
    *   当天一个小时后，若程序还未完全执行完毕，后台场次自动出现补偿开奖机制
    **/    
    public function compenStateOpenGame($game_id){
        //游戏状态
        $this->setGameId($game_id);

        //将现在已经生成的订单作为成功抢购
        $join_user_lists = Db::name('pig_order')->whereTime('establish_time','today')->where('pig_level',$game_id)->field('sell_user_id,purchase_user_id,pig_id')->select();
        $buy_pig_user_list = array_column($join_user_lists,'purchase_user_id');
        $do_pig_list = array_column($join_user_lists,'pig_id'); //已经处理的猪
        $query = Db::name('pig_reservation')->where(['pig_id'=>$this->game_id]);
        //还需要做一个处理。成熟了的并产生订单被人抢的那种

        if(count($buy_pig_user_list) > 0){
            $query->whereNotIn('user_id',$buy_pig_user_list);
        }
        $no_buy_pig = $query->whereTime('reservation_time','today')->select();
        if($no_buy_pig){
            foreach($no_buy_pig as $key => $value){
                accountLog($value['user_id'],0,$value['pay_points'],'抢购失败,预约退回微分',0,0,0,4,$this->game_id);
            }
        }

        //找到所有成熟的宠物$do_pig_list
        $lists = Db::name('user_exclusive_pig')->where('is_able_sale',1)->where('pig_id',$game_id)->whereNotIn('id',$do_pig_list)->select();
        trace('补偿id','game');
        trace($lists,'game');
        foreach($lists as $key => $pig){
            $this->createOrder($pig,$pig['user_id']);
        }

        Db::startTrans();
        $pigbuy = new PigFlashBuy();
        $name = $pigbuy->getFlashName($this->game_id);
        $redis = new Redis();
        $redis->del($name);

        $rs1 = db('pig_goods')->where(['id' =>$this->game_id])->update(['today_is_open' => 1]);
        //将今天改场次所有的猪做处理
        $rs2 = db('user_exclusive_pig')->where('is_able_sale',1)->where('pig_id',$game_id)->update(['is_able_sale'=>0,'appoint_user_id'=>0]);
        //加入一些处理的记录-仅供查找处理
        $data_log['join_user_list'] ='补偿处理';
        $data_log['award_user_list'] = implode(',',!empty($buy_pig_user_list) ?$buy_pig_user_list:[] );
        $data_log['pig_list'] ='补偿处理';
        $data_log['pig_id'] = $this->game_id;
        $data_log['change_time'] = time();
        Db::name('pig_award_log')->insertGetId($data_log);

        if($rs1 && $rs2){
            Db::commit();
            return true;
        }else{
            Db::rollback();
            return false;
        }
    }

    //抢购处理函数
     //抢购处理函数
    public function flashBuyDo($game_id,$ii = 0){

        $_resdis = new Redis();
        $flashtodo =  $_resdis->get('flashtodo') * 1;
        if($flashtodo > 0 && $ii == 0){
            return false;
        }
        $_resdis->set('flashtodo',1);
        $this->setGameId($game_id);
        //获取所有参与的用户
        $pigbuy = new PigFlashBuy();
        $redis_users = [];
        $redis_users = $_tmp =$pigbuy->getUsers($this->game_id);
        $user_lists = $join_user_list = !$redis_users ? [] : array_column($redis_users,'user_id');
        $i = [];
        //trace($redis_users,'game');
        //获取当前游戏的模型
        //找到成熟的猪
        $pig_lists = Db::name('user_exclusive_pig')->where('pig_id',$this->game_id)->where('is_able_sale',1)->order('appoint_user_id desc')->limit(10)->select();

        //还有宠物需要处理的
        if(!empty($pig_lists)){

            foreach($pig_lists as $k =>$pig){
                if(count($user_lists) <= 0 || !$user_lists){
                    //供过于求， 继续走交易流程 --2019-1-14 16:50:48
                    //抢到的人生成订单
                    trace('场次:'.$game_id.'-进入继续繁殖'.$pig['id'],'game');

                    $this->createOrder($pig,$pig['user_id'],2);
                    continue;
                }else{
                    //是否有人被指定的
                    if($pig['appoint_user_id'] && in_array($pig['appoint_user_id'],$user_lists)){
                        foreach($user_lists as $k => $v){
                            if($v == $pig['appoint_user_id']){
                                unset($user_lists[$k]);
                            }
                        }
                        $award_user = $pig['appoint_user_id'];
                    }else{
                        $award_user = array_shift($user_lists);
                        //若果中奖人是自己，且中奖参与人数中还有人的话，那么将自己与下个人的位置调换
                        if($pig['user_id'] == $award_user && count($user_lists) > 0){
                            $position_user = $award_user;
                            $award_user = array_shift($user_lists);
                            array_unshift($user_lists,$position_user);
                        }
                    }

                    if(!empty($award_user)){
                        //抢到的人生成订单

                        $rs = $this->createOrder($pig,$award_user,1);
                        if($rs['status'] == 0){
                            array_unshift($user_lists,$award_user);
                            continue;
                        }
                        $i[] = $award_user;
                        trace('场次:'.$game_id.'-猪:'.$pig['id'].'-抢购人:'.$award_user,'game');

                    }
                }
            }
            if(!empty($i)){
                foreach($i as $k=>$v){
                    $_redis_users = $pigbuy->getUsers($this->game_id);
                    $key = array_search( $v,array_column($_redis_users, 'user_id'));
                    unset($_redis_users[$key]);
                    $pigbuy->resetFlashUser($_redis_users,$this->game_id);
                }
            }

            exec("cd /www/wwwroot/zhugongmeng ;php think doflashbuy {$this->game_id} 1",$a,$b);
        }else{
            $_resdis->set('flashtodo',0);
            //游戏结束
            $name = $pigbuy->getFlashName($this->game_id);
            $redis = new Redis();
            $redis->del($name);
            //开奖状态更新
            db('pig_goods')->where(['id' =>$this->game_id])->update(['today_is_open' => 1]);
            //退预约积分-当天-场次
            $query = Db::name('pig_reservation')->where(['pig_id'=>$this->game_id]);

            $buy_pig_user_list = Redis::smembers($this->game_award_list . $this->game_id);
            if(count($buy_pig_user_list) > 0){
                $query->whereNotIn('user_id',$buy_pig_user_list);
            }
            $no_buy_pig = $query->whereTime('reservation_time','today')->select();
            if($no_buy_pig){
                foreach($no_buy_pig as $key => $value){
                    accountLog($value['user_id'],0,$value['pay_points'],'抢购失败,预约退回积分',0,0,0,4,$this->game_id);
                    //unset($user_lists[$value['user_id']]);
                }
            }

            $this->updateGameStatus(3);
            $join_u = Redis::get('users_list_queue_'.$this->game_id);
            $user_ls = $join_user_list = !$join_u ? [] : array_column($join_u,'user_id');
            //加入一些处理的记录-仅供查找处理
            $data_log['join_user_list'] = implode(',', !empty($user_ls) ?$user_ls:[] );
            $data_log['award_user_list'] = implode(',',!empty($buy_pig_user_list) ?$buy_pig_user_list:[] );
            $data_log['pig_list'] = implode(',',!empty($send_user) ?$send_user:[]);
            $data_log['pig_id'] = $this->game_id;
            $data_log['change_time'] = time();
            Db::name('pig_award_log')->insertGetId($data_log);
            //再次重复处理猪的状态，以免未处理成功
            //db('user_exclusive_pig')->where('id','in',$send_user)->update(['is_able_sale'=>0,'appoint_user_id'=>0]);
        }



    }




}