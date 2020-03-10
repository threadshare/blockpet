<?php
namespace app\common\model;

use think\Db;
use think\Model;

class UserLevel extends Model
{
    public function updateLevel($uid) {
        $userInfo = Db::name('user')->where('id',$uid)->field('id,rc_count,ulevel')->find();
        $money = $userInfo['rc_count'];
        $cnt = Db::name('user_relation')->where('uid',$uid)->value('cnt');
        $map = [];
        $map['money'] = ['elt',$money];
        $map['cnt'] = ['elt',$cnt];
        $levelInfo = Db::name('user_level')
            ->where($map)
            ->order('level','desc')
            ->find();
        if ($levelInfo['level']>$userInfo['ulevel']) {
            Db::name('user')->where('id',$uid)->setField('ulevel',$levelInfo['level']);
        }
    }
    /**
     * 会员升级
     * @param $userInfo
     * @param $shop_integral
     */
    public function upgradeTeam($user_id,$qfz=0)
    {
        $userInfo = Db::name('user')->where('id',$user_id)->find();
        //团队上级
        $parentStr = $userInfo['prel'];
        $parentArr = explode(',',$parentStr);
        //dump($parentArr);
        array_shift($parentArr);
        array_pop($parentArr);
        array_unshift($parentArr,$user_id);

        //dump($parentArr);
        //exit;
        foreach ($parentArr as $id) {
            //用户详情
            $personInfo = Db::name('user')->where('id',$id)->field('id,identity,diligence')->find();

            //团队勤奋值
            //$childqf = Db::name('user')->where('prel','like','%,'.$id.',%')->sum('diligence');

            //$teamDil = $childqf+$personInfo['diligence'];
            //查询对应等级信息
            $levelInfo = Db::name('market_level')->where('tperf','<=',$personInfo['diligence'])->order('tperf','desc')->find();
            if (!$levelInfo || $levelInfo==$personInfo['identity']) continue;
            //人数是否满足
            $dbMap = [];
            $dbMap['identity'] = ['>=',$levelInfo['level']-1];
            $dbMap['prel'] = ['like','%,'.$id.',%'];
            $childNum = Db::name('user')->where($dbMap)->count();
            if ($childNum<$levelInfo['cnt']) continue;

            Db::name('user')->where('id',$id)->setField('identity',$levelInfo['level']);

        }

    }


}