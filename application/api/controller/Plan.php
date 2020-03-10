<?php
/*
'''''''''''''''''''''''''''''''''''''''''''''''''''''''''
源码来自九牛网分享 9nw.cc
''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
 */

namespace app\api\controller;

use think\Controller;
use think\Db;
use app\common\model\User;
use think\Cache;
use think\Log;

/**
 * 计划任务
 * Class Index
 * @package app\api\controller
 */
class Plan extends Controller
{


    /**
     * 预约抢购任务
     * @return mixed
     */
    public function test()
    {
        //检测开奖的猪
        $nowtime = date('H:i:s');
        $map = [];
        $map['start_time'] = ['lt', $nowtime];
        $map['is_open'] = 0;
        $pigInfo = Db::name('task_config')->where($map)->order('start_time')->find();
        dump($pigInfo);
        if (!$pigInfo) die;
        dump(Cache::get('opening'));
        if (Cache::get('opening')) die;
        Cache::set('opening',1);
        Cache::set('is_open'.$pigInfo['id'],0);
        //查找可出售的猪
        $pigMap = [];
        $pigMap['pig_id'] = $pigInfo['id'];
        //$pigMap['from_id'] = 0;
        $pigMap['status'] = 0;
        $map['user_sort'] = ['<>', 0];
        $piglist = Db::name('pig_order')->where($pigMap)->select();
        dump($piglist);
        //查询预约的人
        $userMap = [];
        $userMap['pig_id'] = $pigInfo['id'];
        $userMap['status'] = 0;
        $userMap['buy_type'] = ['<>', 0]; //buy_type0只预约，1只抢购，2预约加抢购    只预约了，是不能参与的
        $userList = Db::name('yuyue')->where($userMap)->order('user_sort,credit_score')->select();
        $redisArr = [];
        $redisName = 'buy_'.date('Ymd').$pigInfo['id'];
        if (!empty($piglist)) {
            //有卖单
            foreach ($piglist as $val) {
                //是否有指定
                if ($val['point_id']) {
                    $uid = $val['point_id'];
                } else {
                    $uid = $this->createUserId($val['pig_id'], $val['uid']);
                    if (!$uid) break;

                }
                //改变订单
                Db::name('pig_order')
                    ->where('id', $val['id'])
                    ->update([
                        'status' => 1,
                        'uid' => $uid,
                        'sell_id' => $val['uid'],
                        'create_time' => time()
                    ]);
                //改变用户猪的状态
                Db::name('user_pigs')->where('id', $val['id'])->setField('status', 3);
                //改变预约状态
                Db::name('yuyue')->where('uid', $uid)->where($userMap)->setField('status', 1);
                array_push($redisArr, $uid);

            }
        }
        //dump($redisArr);die;
       dump(json_encode($redisArr));
        echo $redisName;
        //写入redis
       Cache::set($redisName,$redisArr,86400);
        //改变猪的状态
        Db::name('task_config')->where('id',$pigInfo['id'])->setField('is_open',1);
        dump(Cache::get($redisName));
        Cache::set('is_open'.$pigInfo['id'],1,3600);
        Cache::rm('opening');
        dump(Cache::get('is_open'.$pigInfo['id']));
        //未抢到的
        $this->pointsBack($pigInfo['id']);
    }


    public function createUserId($pig_id, $sell_id)
    {
        $userMap = [];
        $userMap['pig_id'] = $pig_id;
        $userMap['status'] = 0;
        //所有指定的用户ID
        $pointIds = Db::name('user_pigs')->where('point_id', '<>', 0)->column('point_id');
        array_push($pointIds, $sell_id);
        $userMap['uid'] = ['not in', $pointIds];
        $userMap['user_sort'] = ['<>', 0];
        $userMap['buy_type']  = ['<>', 0];//buy_type0只预约，1只抢购，2预约加抢购    只预约了，是不能参与的
        $yuyue = Db::name('yuyue')->where($userMap)->order('user_sort,credit_score', 'desc')->find();
        return $yuyue['uid'];

    }

    /**
     * @param $pig_id 猪ID
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function pointsBack($pig_id)
    {
        $map = [];
        $map['pig_id'] = $pig_id;
        $map['status'] = 0;
        $yuyueList = Db::name('yuyue')->where($map)->select();
        if (!empty($yuyueList)) {
            foreach ($yuyueList as $val) {
                //改变预约状态未中奖
                Db::name('yuyue')->where('id', $val['id'])->setField('status', 2);
                //返回预约积分
                if($val['buy_type']==1){
                    moneyLog($val['uid'], 0, 'pay_points', $val['pay_points'], 4, '抢购未中奖返还');
                }else{
                    moneyLog($val['uid'], 0, 'pay_points', $val['pay_points'], 4, '预约未中奖返还');
                }
            }
        }

    }

    /**
     * 重置游戏
     */
    public function resetGames()
    {
        Log::record('[ resetGames ] 开始执行', 'info');
        $pigList = Db::name('task_config')->field('id,is_open')->select();
        foreach ($pigList as $pig) {
            Db::name('task_config')->where('id', $pig['id'])->setField(['is_open'=>0,'is_flush_open'=>0, 'selled_stock'=>0]);
        }
        Log::record('[ resetGames ] 结束执行', 'info');
    }

    public function userReward()
    {
        Log::record('[ userReward ] 开始执行', 'info');
        $nowTime = time();
        $map = [];
        $map['status'] = 0;
        $map['end_time'] = ['<', $nowTime];
        $pigsList = Db::name('user_pigs')->where($map)->select();
        $config = unserialize(Db::name('system')->where('name', 'base_config')->value('value'));
        //dump($pigsList);
        foreach ($pigsList as $key => $val) {
            $pigReward = $this->pigReward($val['pig_id']);
            $contract_revenue = $val['price'] * $pigReward['contract_revenue'] / 100;
            $doge = $val['price'] * $pigReward['doge'] / 100;
            //收益表记录
            $this->addReward($val['uid'], 0, 'contract_revenue', $contract_revenue, 1, '智能合约');
            //累计收益
            Db::name('user')->where('id', $val['uid'])->setInc('totalmoney', $contract_revenue);
            //增加猪的价值
            model('Pig')->pigUpgarde($val['id'], $contract_revenue);
            $this->addReward($val['uid'], 0, 'doge', $doge, 5, 'DOGE收益');
            moneyLog($val['uid'], 0, 'doge', $doge, 6, 'DOGE收益');
            //上级分成
            $parents = $this->threeParents($val['uid']);
            if ($parents['pid'] > 0) {
                $firstReward = $contract_revenue * $config['firt_parent'] / 100;
                $this->addReward($parents['pid'], $val['uid'], 'share_integral', $firstReward, 2, '一代推广');
                //累计奖励
                Db::name('user')->where('id', $parents['pid'])->setInc('total_share_integral');
                //资产记录
                moneyLog($parents['pid'], $val['uid'], 'share_integral', $firstReward, 4, '一代推广收益');
            }
            if ($parents['pid2'] > 0) {
                $secondReward = $contract_revenue * $config['second_parent'] / 100;
                $this->addReward($parents['pid2'], $val['uid'], 'share_integral', $secondReward, 2, '二代推广');
                //累计奖励
                Db::name('user')->where('id', $parents['pid2'])->setInc('total_share_integral');
                //资产记录
                moneyLog($parents['pid'], $val['uid'], 'share_integral', $secondReward, 4, '二代推广收益');
            }

            if ($parents['pid3'] > 0) {
                $thirdReward = $contract_revenue * $config['third_parent'] / 100;
                $this->addReward($parents['pid3'], $val['uid'], 'share_integral', $thirdReward, 2, '三代推广');
                //累计奖励
                Db::name('user')->where('id', $parents['pid3'])->setInc('total_share_integral');
                //资产记录
                moneyLog($parents['pid'], $val['uid'], 'share_integral', $thirdReward, 4, '三代推广收益');
            }

        }
        Log::record('[ userReward ] 结束执行', 'info');

    }

    public function teamReward()
    {
        $rewardMap = [];
        $rewardMap['create_time'] = ['gt', strtotime(date('Y-m-d'))];
        $rewardMap['type'] = 2;
        $userRewardList = Db::name('user_reward')->where($rewardMap)->select();
        //用户级别
        $levelist = Db::name('user_level')->select();
        $newArr = [];
        foreach ($levelist as $vl) {
            $newArr[$vl['level']] = $vl['ratio'];
        }
        foreach ($userRewardList as $val) {
            $parents = $this->threeParents($val['uid']);
            $teamParentStr = $parents['rel'];
            $parentsArr = explode(',', $teamParentStr);
            //去空去0
            array_shift($parentsArr);
            array_pop($parentsArr);
            if (!empty($parentsArr)) {
                foreach ($parentsArr as $parent) {
                    //级别
                    $parentLevel = Db::name('user')->where('id', $parent)->value('ulevel');
                    $parentReward = $val['amount'] * $newArr[$parentLevel] / 100;
                    if($parentReward > 0){
                        //收益记录
                        $this->addReward($parent, $val['uid'], 'share_integral', $parentReward, 3, '团队收益');
                        //累计收益
                        Db::name('user')->where('id', $parent)->setInc('total_share_integral', $parentReward);
                        //资金记录
                        moneyLog($parent, $val['uid'], 'share_integral', $parentReward, 5, '团队收益');
                    }
                }
            }

        }
    }

    /**
     * 两小时未付款订单取消
     */
    public function orderCan()
    {
        $map['status'] = 1;
        $map['create_time'] = ['elt', time() - 3600];
        $list = Db::name('pig_order')->where($map)->select();
        if (!$list) exit;
        foreach ($list as $val) {
            model('PigOrder')->cancel($val['id']);
            //冻结帐号
            Db::name('user')->where('id',$val['uid'])->setField('status', 0);
        }
    }

    /**
     * 强制交易
     */
    public function orderCon()
    {
        $map['status'] = 2;
        $map['create_time'] = ['elt', time() - 3600];
        $map['is_lock'] = 0;
        $list = Db::name('pig_order')->where($map)->column('id');
        if (!$list) exit;
        foreach ($list as $val) {
            model('PigOrder')->confirm($val);
        }
    }


    /**
     * 添加收益记录
     * @param $uid 用户ID
     * @param $from_id 来源ID
     * @param $currency 币种
     * @param $amount 数目
     * @param $type 类型
     * @param $note 说明
     */
    public function addReward($uid, $from_id, $currency, $amount, $type, $note)
    {
        $rewardLog = [];
        $rewardLog['uid'] = $uid;
        $rewardLog['from_id'] = $from_id;
        $rewardLog['currency'] = $currency;
        $rewardLog['amount'] = $amount;
        $rewardLog['type'] = $type;
        $rewardLog['note'] = $note;
        $rewardLog['create_time'] = time();
        Db::name('user_reward')->insert($rewardLog);
    }

    /**
     * 用户关系
     * @param $uid 用户ID
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public function threeParents($uid)
    {
        $relation = Db::name('user_relation')->where('uid', $uid)->find();
        return $relation;
    }

    /**
     * 不同级别的猪对应的奖金
     * @param $id 猪ID
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public function pigReward($id)
    {
        $pigInfo = Db::name('task_config')->where('id', $id)->field('id,contract_revenue,doge')->find();
        return $pigInfo;
    }
}

