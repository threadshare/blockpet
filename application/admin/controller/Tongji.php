<?php
/*
'''''''''''''''''''''''''''''''''''''''''''''''''''''''''
author:ming    contactQQ:811627583
''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
 */
namespace app\admin\controller;

use app\common\controller\AdminBase;
use think\Cache;
use think\Db;

/**
 * 统计报表
 * Class System
 * @package app\admin\controller
 */
class Tongji extends AdminBase
{
    public $begin;
    public $end;
    public function _initialize(){
        parent::_initialize();
       $searchDate = $this->request->param();
        //$time = explode(' - ',urldecode($this->request->param('timegap')));
        $start_time = !empty($searchDate['start_time']) ? $searchDate['start_time'] : '';
        $end_time   = !empty($searchDate['end_time']) ? $searchDate['end_time'] : '';
        if($start_time){
            $begin = urldecode($start_time);
            $end   = urldecode($end_time);
        }else{
            $begin = date('Y-m-d', strtotime("-3 month"));//30天前
            $end   = date('Y-m-d', strtotime('+1 day'));
        }

        $this->assign('start_time',$begin);
        $this->assign('end_time',$end);
        $this->begin = strtotime($begin);
        $this->end = strtotime($end)+86399;
    }
    public function user()
    {
        $today = strtotime(date('Y-m-d'));
        $month = strtotime(date('Y-m-01'));
        $user['today'] = Db::name('user')->where("create_time>$today")->count();//今日新增会员
        $user['month'] = Db::name('user')->where("create_time>$month")->count();//本月新增会员
        $user['total'] = Db::name('user')->count();//会员总数
//        $user['user_money'] = Db::name('user')->sum('user_money');//会员余额总额
        $res = Db('pig_order')->cache(true)->distinct(true)->field('uid')->select();
         $user['hasorder'] = count($res);
        $this->assign('user',$user);
        $sql = "SELECT COUNT(*) as num,FROM_UNIXTIME(create_time,'%Y-%m-%d') as gap from wym_user where create_time>$this->begin and create_time<$this->end group by gap";
        $new = DB::query($sql);//新增会员趋势
        foreach ($new as $val){
            $arr[$val['gap']] = $val['num'];
        }
        $brr = $day = [];
        for($i=$this->begin;$i<=$this->end;$i=$i+24*3600){
            $brr[] = empty($arr[date('Y-m-d',$i)]) ? 0 : $arr[date('Y-m-d',$i)];
            $day[] = date('Y-m-d',$i);
        }
        $result = array('data'=>$brr,'time'=>$day);
        //dump($result);die;
        $this->assign('result',json_encode($result));

        return view();
    }
    public function pet()
    {
        $today = strtotime(date('Y-m-d'));
        $month = strtotime(date('Y-m-01'));
        $user['today'] = Db::name('user_pigs')->where("create_time>$today")->count();//今日新增宠物
        $user['month'] = Db::name('user_pigs')->where("create_time>$month")->count();//本月新增宠物
        $user['total'] = Db::name('user_pigs')->count();//会员总数
//        $user['user_money'] = Db::name('user')->sum('user_money');//会员余额总额
        $res = Db('pig_order')->cache(true)->distinct(true)->field('uid')->select();
        $user['hasorder'] = count($res);
        $this->assign('user_pig',$user);
        $sql = "SELECT COUNT(*) as num,FROM_UNIXTIME(create_time,'%Y-%m-%d') as gap from wym_user_pigs where create_time>$this->begin and create_time<$this->end group by gap";
        $new = DB::query($sql);//新增趋势
        foreach ($new as $val){
            $arr[$val['gap']] = $val['num'];
        }
        $brr = $day = [];
        for($i=$this->begin;$i<=$this->end;$i=$i+24*3600){
            $brr[] = empty($arr[date('Y-m-d',$i)]) ? 0 : $arr[date('Y-m-d',$i)];
            $day[] = date('Y-m-d',$i);
        }
        $result = array('data'=>$brr,'time'=>$day);
        //dump($result);die;
        $this->assign('result',json_encode($result));

        return view();
    }

}