<?php
/*
'''''''''''''''''''''''''''''''''''''''''''''''''''''''''
author:ming    contactQQ:811627583
''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
 */
namespace app\admin\controller;

use app\common\controller\AdminBase;
use think\Config;
use think\Db;
use think\Session;
/**
 * 管理员管理
 * Class AdminUser
 * @package app\admin\controller
 */
class Wealth extends AdminBase
{
    protected function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 全部记录
     * @return mixed
     */
    public function index()
    {
        $map['id']=['gt',0];
        if($this->request->get('username')){
            $user_id = Db::name('user')->where('mobile',$this->request->get('username'))->value('id');
//            $map['username']=$this->request->get('username');
            $map['user_id'] = $user_id;
        }
        if($this->request->get('note')){
            $mark=$this->request->get('note');
            $map['note']=array('like',"%{$mark}%");
        }
//        $list=Db::name('Wealth')->order('create_time desc')->where($map)->paginate(12,false,['query'=>$this->request->param()]);
        $list = model('Wealth')
            ->where($map)
            ->order('create_time desc')
            ->paginate(12,false,['query'=>$this->request->param()]);
//            ->each(function($item, $key){
//                $user_id = $item["user_id"]; //获取数据集中的id
//                $username = Db::name('user')->where("id='$user_id'")->value('username'); //根据ID查询相关其他信息
//                $item['username'] = $username; //给数据集追加字段num并赋值
//                return $item;
//            });
        $this->assign('list',$list);
        $this->assign('page',$list->render());
        return $this->fetch();
    }






    /**
     * 系统充值扣款
     * @return mixed
     */
    public function setWealth()
    {
        $id = $this->request->param('id');
        $user = Db::name('user')->where('id',$id)->field('id,mobile')->find();
        if($this->request->isPost()){
            $data=$this->request->post();
            //dump($data);die;
            //$uid=Db::name('user')->where('mobile',$data['username'])->value('id');
            if(!is_numeric($data['num']) ){
                $this->error('数量填写错误');
            }


//            if(!$uid){
//                $this->error('用户不存在');
//            }

            if($data['type']=='add'){
//                多账号
//                $money[$data['money_type']]=$data['num'];
                $money = $data['num'];
                $mark='系统充值';
            }else{
//                多账号
//                $money[$data['money_type']]=-$data['num'];
                $money = -$data['num'];
                $mark='系统扣除';
            }

            //dump($money);die;
            $res = moneyLog($id,0,$data['money_type'],$money,1,$mark);
            if ($res) {
                $this->success('操作成功');
            } else {
                $this->error('操作失败');
            }
        }
        $this->assign('userInfo',$user);
        return $this->fetch();
    }

    /**
     * 激活申请
     * @return mixed
     */
    public function applyActivate()
    {
        $map = [];
        if ($this->request->get('username')) {
            $map['username'] = $this->request->get('username');
        }
        $list = Db::name('apply_activate')
                ->where($map)
                ->order('id','desc')
                ->paginate(12,false,['query'=>$this->request->param()]);
//        echo Db::table('wym_apply_recharge')->getLastSql();die;
        $this->assign('list',$list);
        $this->assign('page',$list->render());
        return $this->fetch();
    }

    /**
     * 激活审核
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function audActivate()
    {
        $id = $this->request->param('id');
        $applyInfo = Db::name('apply_activate')->find($id);
        $res = model('User')->act($applyInfo['user_id']);
        if ($res) {
            //修改申请状态
            Db::name('apply_activate')->where('id',$id)->setField('status',1);
            $this->success('操作成功');
        } else {
            $this->error('操作失败');
        }
    }

    /**
     * 充值记录
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function applyRecharge()
    {
        $map = [];
        if ($this->request->get('username')) {
            $map['mobile'] = $this->request->get('username');
        }
//        dump($map);die;
        $list = Db::name('zc_order')
                ->where($map)
                ->order('id','desc')
                ->paginate(12,false,['query'=>$this->request->param()]);
//        dump($list);
//        echo Db::table('wym_apply_recharge')->getLastSql();die;
        $this->assign('list',$list);
        $this->assign('page',$list->render());
        return $this->fetch();
    }

    public function audRecharge()
    {
        $id = $this->request->param('id');
        $rechargeInfo = Db::name('zc_order')->find($id);
        // 启动事务


        //  改变订单状态
            $res1 = Db::name('zc_order')->where('id',$id)->setField('status',1);

            if ($res1) {
                $res2 = moneyLog($rechargeInfo['uid'],0,'pay_points',$rechargeInfo['num'],1,'系统充值');
                if ($res2) {
                    //累加充值
                    Db::name('user')->where('id',$rechargeInfo['uid'])->setInc('rc_count',$rechargeInfo['num']);
                    $this->success('操作成功');
                } else {
                    Db::name('zc_order')->where('id',$id)->setField('status',0);
                    $this->error('操作失败');
                }
            }



    }

    /**
     * 充值方式
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function rechargeMode()
    {
        $list = Db::name('recharge_mode')->order('id','desc')->paginate(12);
        $this->assign('list',$list);
        $this->assign('page',$list->render());
        return $this->fetch();
    }


    /**
     * 支付方式修改
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function editRecharge()
    {
        $info = Db::name('recharge_mode')->find();
        if ($this->request->isPost()) {
           $data = request()->post();
           //dump($data);die;
           if (empty($data['img'])) {
                $this->error('二维码不能为空');
           }

            $saveInfo=[];
           switch ($data['type']) {
               case 1:
                   $field = 'wechat_img';
                   $account='wechat_account';
                   break;
               case 2:
                   $field = 'alipay_img';
                   $account = 'alipay_account';
                   break;
               case 3:
                   $field = 'server_img';

                   break;
           }
            if ($data['type']==1 || $data['type']==2) {
                empty($data['account']) ? $this->error('账号不能为空') : '';
                $saveInfo[$account] = $data['account'];
            }
           $saveInfo[$field] = $data['img'];
           if ($info) {
               $res = Db::name('recharge_mode')->where('id',$info['id'])->update($saveInfo);
           } else {
               $res = Db::name('recharge_mode')->insert($saveInfo);
           }

           if ($res) {
               $this->success('操作成功','');
           } else {
               $this->error('操作失败');
           }
        }

//        dump($info);die;
        $this->assign('info',$info);
        return $this->fetch();
    }

    /**
     * 微信客服
     * @return mixed
     */
    public function wechatService()
    {
        $info = Db::name('recharge_mode')->find();



        $this->assign('info',$info);
        return $this->fetch();
    }

    /**
     * QQ客服
     * @return mixed
     */
    public function qqService()
    {
        $info = Db::name('recharge_mode')->find();
        if ($this->request->isPost()) {
            $data = request()->post();
//            if (empty($data['rname'])) {
//                $this->error('支付方式不能为空');
//            }
            if (empty($data['qq_img'])) {
                $this->error('支付二维码不能为空');
            }
            if ($info) {
                $res = Db::name('recharge_mode')->where('id',$info['id'])->update($data);
            } else {
                $res = Db::name('recharge_mode')->insert($data);
            }

            if ($res) {
                $this->success('操作成功','');
            } else {
                $this->error('操作失败');
            }
        }

//        dump($info);die;
        $this->assign('info',$info);
        return $this->fetch();
    }

    /**
     * 平台支付方式删除
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function delRecharge()
    {
//        dump($_GET);die;
        $id = request()->param('id');
        $res = Db::name('recharge_mode')->delete($id);
        if ($res) {
            $this->success('操作成功');
        } else {
            $this->error('操作失败');
        }
    }

    public function withdrawList()
    {
        $list = Db::name('withdraw')->order('id','desc')->paginate(12);
        $this->assign('list',$list);
        return $this->fetch();
    }

    /**
     * 交易列表
     * @return \think\response\View
     * @throws \think\exception\DbException
     */
    public function tradeList()
    {
        $mobile = $this->request->param('username');
        //dump($mobile);die;

        $map = [];
        if ($mobile) {
            $id = Db::name('user')->where('mobile',$mobile)->value('id');
            $map['buy_id|sell_id'] = $id;
        }
        $list = Db::name('trade_order')
            ->where($map)
            ->order('id','desc')
            ->paginate(12,false,['query'=>$this->request->param()])
            ->each(function($item, $key){
                $buy_id = $item["buy_id"]; //获取数据集中的id
                $sell_id = $item['sell_id'];
                $buyname = Db::name('user')->where("id='$buy_id'")->value('realname'); //根据ID查询相关其他信息
                $item['buyname'] = $buyname; //给数据集追加字段num并赋值
                $sellname = Db::name('user')->where("id='$sell_id'")->value('realname'); //根据ID查询相关其他信息
                $item['sellname'] = $sellname; //给数据集追加字段num并赋值
                return $item;
            });
        return view()->assign(['list'=>$list,'page'=>$list->render()]);
    }

    /**
     * 交易订单删除
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function orderDel()
    {
        $id = $this->request->param('id');
        //dump($id);
        $orderInfo = Db::name('trade_order')->where('id',$id)->find();
        $baseConfig = unserialize(Db::name('system')->where('name','base_config')->value('value'));
        $amount = $orderInfo['num']*(1+$baseConfig['trade_sxf']/100);
        $moneyResult = moneyLog($orderInfo['sell_id'],0,'fruit',$amount,9,'交易取消');
        if ($moneyResult) {
            $config=unserialize(Db::name('system')->where('name','base_config')->value('value'));
            $pool = Db::name('bonus_pool')->find();
            Db::name('bonus_pool')
                ->where('id',$pool['id'])
                ->update([
                    'bonus_pool'=>$pool['bonus_pool']-$orderInfo['num']*$config['bonus_ratio']/100 ,
                    'welfare_pool' => $pool['welfare_pool']-$orderInfo['num']*$config['welfare_ratio']/100,
                    'project_pool' => $pool['project_pool']-$orderInfo['num']*$config['project_ratio']/100,
                    'foam' => $pool['foam']-$orderInfo['num']*$config['foam_ratio']/100,
                ]);
            $res = Db::name('trade_order')->where('id',$id)->delete();
            //if ($res)
            $res ? $this->success('取消成功') : $this->error('操作失败');
        }

    }

    public function orderConfirm()
    {
        $id = $this->request->param('id');
        //dump($id);die;
        $orderInfo =  Db::name('trade_order')->where('id',$id)->find();
        $moneyResult = moneyLog($orderInfo['buy_id'],0,'fruit',$orderInfo['num'],10,'系统确认交易');
        if ($moneyResult) {
            //改变订单状态
            $res = Db::name('trade_order')->where('id',$id)->setField('status',3);
            $res ? $this->success('操作完成') : $this->error('操作失败');
        }
    }


}