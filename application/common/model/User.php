<?php
/*
'''''''''''''''''''''''''''''''''''''''''''''''''''''''''
源码来自九牛网分享 9nw.cc
''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
 */
namespace app\common\model;

use think\Model;
use think\Db;

class User extends Model
{
    protected $insert = [];
    protected $readonly = ['username','create_time','pid','pusername'];

    protected $baseConfig;

    protected function initialize()
    {
        //需要调用`Model`的`initialize`方法
        parent::initialize();
        //TODO:自定义的初始化
        $this->baseConfig = unserialize(Db::name('system')->where('name','base_config')->value('value'));
    }
     /**
     * 注册
     * @return bool|string
     */
    public function reg($data=null)
    {
    	if($data){
    		$re=$this->allowField(true)->save($data);
    	}else{
    		$request=request();
    		$data = $request->post();
            if(!isset($data['data'])){
                $data['data'] = $data;
            }
    		$info = $data['data'];

    		$info['username'] = $info['mobile'];
    		$info['nickname'] = $info['nickname'];
    		$info['reg_ip'] =$_SERVER['REMOTE_ADDR'];
//            if (empty($data['pwd_pay'])) {
//                $data['pwd_pay'] = '666666';
//            }
            $info['create_time'] = time();
    		$re=$this->allowField(true)->save($info);
    		if ($re) {
    		    $pusername = $info['pusername'];
    		    if (!$this->getByMobile($info['pusername'])) {
    		        $pusername = 0;
                }
    		    model('UserRelation')->createRelation($this->id,$pusername);
            }
    	}
    	return $this->id;
    }

    public function UserInfo ($username) {
        $userInfo = $this->getByUsername($username);
        return $userInfo;
    }


    public function getUserLevelAttr($level)
    {
        $userLevel = [
          1 => '会员',
          2 => '初级合伙人',
          3 => '中级合伙人',
          4 => '高级合伙人'
        ];
        return $userLevel[$level];
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
     * 密码
     * @return bool|string
     */
    protected function setPasswordAttr($value)
    {
        return md5($value.config('salt'));
    }

    protected function setPwdPayAttr($val)
    {
        return md5($val.config('salt'));
    }

    /**
     * 团队人数达标奖励
     * @param $user_id
     * @throws \think\Exception
     */
    public function sunTeamForward ($user_id)
    {
        //所有上级用户
        $parentStr = Db::name('user')->where('id',$user_id)->value('prel');
        $parentArr = explode(',',$parentStr);
        array_shift($parentArr);
        array_pop($parentArr);
        foreach ($parentArr as $key=>$val) {
            $childCount = $this->sunTeamCount($val);
            if ($childCount == 500) {
                //增加资产
                $money = 100;
                Db::name('user')->where('id',$val)->setInc('money',$money);
                //  资产记录
                $moneyLog = [
                    'user_id' =>$val,
                    'money' =>$money,
                    'type' =>4,
                    'note' =>'团队达标500人奖励',
                    'create_time'=>date('Y-m-d H:i:s')
                ];
                Db::name('money_log')->insert($moneyLog);
            } elseif ($childCount ==1500) {
                $money = 1000;
                Db::name('user')->where('id',$val)->setInc('money',$money);
                //  资产记录
                $moneyLog = [
                    'user_id' =>$val,
                    'money' =>$money,
                    'type' =>4,
                    'note' =>'团队达标1500人奖励',
                    'create_time'=>date('Y-m-d H:i:s')
                ];
                Db::name('money_log')->insert($moneyLog);
            }
        }
    }
    /**
     * 团队用户统计
     * @param $user_id
     * @return int|string
     */
    public function sunTeamCount($user_id)
    {
        $count = Db::name('user')->where('prel','like','%,'.$user_id.',%')->count();
        return $count;
    }

    /**
     * 团队会员明细
     * @param $user_id
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function teamInfo($user_id)
    {
        $teamInfo = Db::name('user')->where('prel','like','%,'.$user_id.',%')->field('username')->select();
        return $teamInfo;
    }


    /**
     * 按充值金额统计业绩
     * @param $user_id
     * @return float|int
     */
    public function teamReacharge($user_id)
    {
        $childs = Db::name('user')->where('prel','like','%,'.$user_id.',%')->column('id');
//        dump($childs);
        $total = Db::name('money_log')->where(['user_id'=>['in',$childs],'type'=>['in',[2,5]]])->sum('money');
//        dump($total);die;
        return $total;
    }

    //根据上级级数分发分成
    public function parentForward($user_id,$money)
    {
        $userPrel = $this->where('id',$user_id)->value('prel');
        $prelArr = explode(',',$userPrel);
        array_pop($prelArr);
        array_shift($prelArr);
        foreach ($prelArr as $key=>$val) {
            echo $key;
            echo '<br/>';
        }
        dump($prelArr);die;
    }
}