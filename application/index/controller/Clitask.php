<?php
namespace app\index\controller;
use app\common\model\MemberModel;
use think\Controller;

use think\Db;
use think\facade\Session;

use think\facade\Request;

/**
 * 定时任务
 * Class Clitest
 * @package app\index\controller
 */
class Clitask extends Controller
{

    /**
     * 团队奖励
     */
   public function teamForward()
   {
       $levelList = Db::name('market_level')->field('id,level,reward')->select();
       $pools = Db::name('bonus_pool')->find();
       $moneyAmount = $pools['bonus_pool'];
       //dump($moneyAmount);die;
       foreach ($levelList as $val) {
           //查询当前等级会员
           $users = Db::name('user')->where('identity',$val['level'])->field('id,identity')->select();
           if (empty($users)) continue;
           $userNum = count($users);
           $userForward = ($moneyAmount*$val['reward']/100)/$userNum;
           foreach ($users as $uv) {
               $maxForward = model('User')->userMaxFruit($userForward,$uv['id']);
               //dump($maxForward);
               if ($maxForward>0)moneyLog($uv['id'],0,'fruit',$userForward,7,'团队奖励');
               //减少奖励池
               Db::name('bonus_pool')->where('id',$pools['id'])->setDec('bonus_pool',$maxForward);
           }
       }

   }

    /**
     * 个人静态奖励
     */
   public function personForward()
   {
       $levelList = Db::name('user_level')->field('id,level,ratio')->select();
       $pools = Db::name('bonus_pool')->find();
       $moneyAmount = $pools['bonus_pool'];
       foreach ($levelList as $val) {
           //查询当前等级会员
           $users = Db::name('user')->where('user_level',$val['level'])->field('id,identity')->select();
           if (empty($users)) continue;
           $userNum = count($users);
           $userForward = ($moneyAmount*$val['ratio']/100)/$userNum;
           foreach ($users as $uv) {
               //是否获得了团队奖励
               $log = Db::name('money_log')->where(['user_id'=>['=',$uv['id']],'type'=>['=',7],'create_time'=>['>',date('Y-m-d 0:0:0')]])->find();
               if ($log) continue;
               $maxForward = model('User')->userMaxFruit($userForward,$uv['id']);
               if ($maxForward>0) moneyLog($uv['id'],0,'fruit',$maxForward,7,'个人静态奖励');
           }
       }
   }

   public function certAuth()
   {
       $idcard = '1111111';
       $realname = '张三';
       model('Authentication')->authInfo($idcard,$realname);
   }

}
