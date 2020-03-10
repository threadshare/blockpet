<?php
/*
'''''''''''''''''''''''''''''''''''''''''''''''''''''''''
源码来自九牛网分享 9nw.cc
''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
 */
namespace app\index\controller;

use app\common\controller\IndexBase;
use think\Controller;
use think\Db;
use My\DataReturn;


class User extends IndexBase
{
    //protected $pigInfo=['id'= 'start']
    //首页
    public function index()
    {
        //总资产（猪的价值）
        $uid = $this->user_id;
        $zMap = [];
        $zMap['uid'] = $uid;
        $zMap['status'] = 1;
        $user_pigs = Db::name('user_pigs')->where($zMap)->sum('price');
        //合约收益
        $map = [];
        $map['uid'] = $uid;
        $map['type'] = 1;
        $contract_revenue = Db::name('user_reward')->where($map)->sum('amount');
        //领养次数
        //$uid = $this->user_id;
        $adoptcount = Db::name('pig_order')->where('uid',$uid)->count();
        //转让次数
        $trancount = Db::name('pig_order')->where('sell_id',$uid)->count();
        $userlevel = Db::name('user_level')->where('level',$this->user['ulevel'])->field('name')->find();
        return view()
            ->assign([
                'user_pigs'=>$user_pigs,
                'contract_revenue'=>$contract_revenue,
                'trancount' => $trancount,
                'adoptcount' => $adoptcount,
                'userlevel'=>$userlevel
                ]);
    }

    public function isAuthApi(){
        $uid = $this->user_id;
        $isAuth = $this->isAuth() ? 1 : 0;
        $hasBank = Db::name('user_payment')->where(['uid'=>$uid, 'type' => 3])->find() ? 1 : 0;
        //是否待审核
        $isStatus = Db::name('identity_auth')->where(['uid'=>$uid])->value('status');
        DataReturn::returnJson(200,'',['isAuth'=>$isAuth,'hasBank'=>$hasBank,'isStatus'=>$isStatus]);
    }

    public function isAuth()
    {
        $user_id = $this->user_id;
        $map = [];
        $map['uid'] = $user_id;
        $map['status'] = 1;
        if (Db::name('identity_auth')->where($map)->find()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 设置
     * @return \think\response\View
     */
    public function set()
    {
        return view();
    }
    public function set_nickname()
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            //dump($data);
            empty($data['data']['nickname']) ? $this->error('请输入昵称') : '';
            $res = Db::name('user')->where('id',$this->user_id)->setField('nickname',$data['data']['nickname']);
            $res ? $this->success('修改成功') : $this->error('您没做任何修改');
        }
        return view();
    }
    /**
     * PIG
     * @return \think\response\View
     */
    public function pig_currency()
    {
        $map = [];
        $map['user_id'] = $this->user_id;
        $map['currency'] = 'wia';
        $list = Db::name('money_log')->where($map)->select();
        return view()->assign(['piglist'=>$list]);
    }

    /**
     * PIG提币
     * @return \think\response\View
     */
    public function pig_draw()
    {
        $config = unserialize(Db::name('system')->where('name','base_config')->value('value'));
        if ($this->request->isPost()) {
            $data = $this->request->post();
            //dump($data);die;
            if ($data['data']['number']<0 || !is_numeric($data['data']['number'])) $this->error('数目不合法');
            if ($this->user['pig'] < $data['data']['number']) $this->error('wia不足');
            $saveDate = [];
            $saveDate['uid'] = $this->user_id;
            $saveDate['mobile'] = $this->user['mobile'];
            $saveDate['currency'] = 'wia';
            $saveDate['wallet_address'] = $data['data']['wallet_address'];
            $saveDate['num'] = $data['data']['number'];
            $saveDate['tx_rate']  = $config['pig_sxf'];
            $saveDate['sxf'] = $saveDate['num']*$config['pig_sxf']/100;
            $saveDate['realmoney'] = $saveDate['num']-$saveDate['sxf'];
            $saveDate['create_time'] = time();
            $re = Db::name('tixian')->insert($saveDate);
            if ($re) {
                moneyLog($this->user_id,$this->user_id,'wia',-$saveDate['num'],7,'wia提币');
                $this->success('操作成功，待系统确认');
            } else {
                $this->error('操作失败');
            }

        }

        return view()->assign(['pig_sxf'=>$config['pig_sxf']]);
    }

    /**
     * DOGE
     * @return \think\response\View
     */
    public function doge_money()
    {
        $map = [];
        $map['user_id'] = $this->user_id;
        $map['currency'] = 'doge';
        $list = Db::name('money_log')->where($map)->select();
        return view()->assign(['doge_list'=>$list]);
    }

    /**
     * DOGE提币
     * @return \think\response\View
     */
    public function doge_draw()
    {
        $config = unserialize(Db::name('system')->where('name','base_config')->value('value'));
        if ($this->request->isPost()) {
            $data = $this->request->post();
            //dump()
            if ($data['data']['number']<0 || !is_numeric($data['data']['number'])) $this->error('数目不合法');
            if ($this->user['doge'] < $data['data']['number']) $this->error('DOGE不足');
            $saveDate = [];
            $saveDate['uid'] = $this->user_id;
            $saveDate['mobile'] = $this->user['mobile'];
            $saveDate['currency'] = 'doge';
            $saveDate['wallet_address'] = $data['data']['wallet_address'];
            $saveDate['num'] = $data['data']['number'];
            $saveDate['tx_rate']  = $config['doge_sxf'];
            $saveDate['sxf'] = $saveDate['num']*$config['doge_sxf']/100;
            $saveDate['realmoney'] = $saveDate['num']-$saveDate['sxf'];
            $saveDate['create_time'] = time();
            $re = Db::name('tixian')->insert($saveDate);
            if ($re) {
                moneyLog($this->user_id,$this->user_id,'doge',-$saveDate['num'],7,'DOGE提币');
                $this->success('操作成功，待系统确认');
            } else {
                $this->error('操作失败');
            }

        }
        return view()->assign(['doge_tx_sxf'=>$config['doge_sxf']]);
    }

    /**
     * 微分
     * @return \think\response\View
     */
    public function blessings_log()
    {
        $logMap = [];
        $logMap['user_id'] = $this->user_id;
        $logMap['currency'] = 'pay_points';
        $loglist = Db::name('money_log')->where($logMap)->order('id','desc')->select();
        $this->assign('loglist',$loglist);
//        dump($loglist);die;
        return view();
    }

    /**
     * 微分充值
     * @return \think\response\View
     */
    public function blessings_recharge ()
    {
        $rechargeMode = Db::name('recharge_mode')->find();
        if ($this->request->isPost()) {
            $data = $this->request->post();
            //dump($data);
            //if (!$this->user['pay_password']) $this->error('请先设置二级密码');
            if ($data['data']['number']<0 || !is_numeric($data['data']['number'])) {
                $this->error('数目不合法');
            }
            empty($data['data']['imgs']) ? $this->error('请上传支付凭证') :'';
            $saveDate = [];
            $saveDate['uid'] = $this->user_id;
            $saveDate['num'] = $data['data']['number'];
            $saveDate['voucher'] = $data['data']['imgs'];
            $saveDate['mobile'] = $this->user['mobile'];
            $saveDate['create_time'] = time();
            $res = Db::name('zc_order')->insert($saveDate);
            $res ? $this->success('申请成功，待系统确认') : $this->error('操作失败');
        }
        return view()->assign('ewm',$rechargeMode);
    }

    /**
     * 微分转增
     * @return \think\response\View
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function blessings_transfer()
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            //dump($data);
            if (!$this->user['pay_password']) $this->error('请先设置二级密码');
            $password = $data['data']['password'];
            if (md5($password.config('salt')) != $this->user['pay_password']) {
                $this->error('二级密码不正确');
            }
            $number = $data['data']['number'];
            if (!is_numeric($number) || $number<0 ) $this->error('数目不合法');
            if ($number > $this->user['pay_points']) $this->error('微分不足');
            //用户检测
            $mobile = $data['data']['mobile'];
            $tranUser = Db::name('user')->where('mobile',$mobile)->find();
            if (!$tranUser) $this->error('用户不存在');
            $userRelation = Db::name('user_relation')->where('uid',$tranUser['id'])->find();
            //dump($userRelation);die;
            $relArr = explode(',',$userRelation['rel']);
            if (!in_array($this->user_id,$relArr)) $this->error('只能转入团队下级');
            //减少当前用户
            moneyLog($this->user_id,$tranUser['id'],'pay_points',-$number,8,'微分转增');
            //增加转让用户
            moneyLog($tranUser['id'],$this->user_id,'pay_points',$number,8,'微分转增');
            //累计充值
            Db::name('user')->where('id',$tranUser['id'])->setInc('rc_count',$number);
            //会员等级
            model('UserLevel')->updateLevel($tranUser['id']);
            $this->success('转增成功');
        }
        return view();
    }

    /**
     * 合约收益
     * @return \think\response\View
     */
    public function profit_log()
    {
        $map = [];
        $map['uid'] = $this->user_id;
        $map['type'] = 1;
        $logList = Db::name('user_reward')->where($map)->order('id','desc')->select();
        $amount = Db::name('user_reward')->where($map)->sum('amount');
        return view()->assign(['loglist'=>$logList,'amount'=>$amount]);
    }

    /**
     * 推广收益
     * @return \think\response\View
     */
    public function profit()
    {
        $map = [];
        $map['user_id'] = $this->user_id;
        $map['currency'] = 'share_integral';
        $logList = Db::name('money_log')->where($map)->limit(100)->order('id','desc')->select();
        return view()->assign('loglist',$logList);
    }

    /**
     * 推广收益售出
     * @return \think\response\View
     */
    public function sell()
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            //dump($data);
            $baseConfig = unserialize(Db::name('system')->where('name','base_config')->value('value'));
            //出售次数
            $slMap = [];
            $slMap['user_id'] = $this->user_id;
            $slMap['type'] = 2;
            $slMap['create_time'] = ['gt',date('Y-m-d 0:0:0')];
            $userSellCount = Db::name('money_log')->where($slMap)->count();
//            dump($baseConfig['sell_num']);
//            dump($baseConfig['sell_num']<$userSellCount);
//            dump($userSellCount);die;
            if ($userSellCount>$baseConfig['sell_num']) $this->error('每天最多售出'.$baseConfig['sell_num'].'次');
            if (!$this->user['pay_password']) $this->success('请先设置二级密码','set_paypwd');
            if (md5($data['data']['paypwd'].config('salt')) != $this->user['pay_password']) $this->error('二级密码不正确');

            //数目检测
            if ($data['data']['number']<=0 || !is_numeric($data['data']['number'])) $this->error('数目不合法');
            if ($data['data']['number']>$this->user['share_integral']) $this->error('余额不足');
            //最大值
            $maxPrice = Db::name('task_config')->max('max_price');
            //最小值
            $minPrice = $baseConfig['sell_min'];

            if ($data['data']['number']>$maxPrice || $data['data']['number']<$minPrice) {
                $this->error('请输入'.$minPrice.'--'.$maxPrice.'的出售数目');
            }
            //检测对应的猪的级别
            if(isset($data['data']['pid'])&&$data['data']['pid']){
                $pigInfo = model('Pig')->where(['id'=>$data['data']['pid']])->find();

                if($pigInfo['max_price']<$data['data']['number'] || $pigInfo['min_price']>$data['data']['number']){
                    $this->error('请输入'.$pigInfo['name'].'的出售区间'.$pigInfo['min_price'].'--'.$pigInfo['max_price']);
                }
            }else{
                $pigInfo = model('Pig')->pigLevel($data['data']['number']);
            }
            if($pigInfo['selled_stock'] < $pigInfo['max_stock'] ){
                //扣減库存
                model('Pig')->where(['id'=>['eq',$pigInfo['id']], 'selled_stock'=>['lt', $pigInfo['max_stock']]])->setInc('selled_stock');
                //dump($pigInfo);
                $saveDate = [];
                $saveDate['uid'] = $this->user_id;
                $saveDate['pig_id'] = $pigInfo['id'];
                $saveDate['pig_name'] = $pigInfo['name'];
                $saveDate['price'] = $data['data']['number'];
                $saveDate['contract_revenue'] = $pigInfo['contract_revenue'];
                $saveDate['cycle'] = $pigInfo['cycle'];
                $saveDate['doge'] = $pigInfo['doge'];
                $saveDate['pig_no'] = create_trade_no();
                $saveDate['status'] = 1;
                $saveDate['create_time'] = time();
                $saveDate['end_time'] = time()+$pigInfo['cycle']*24*3600;
                $sell_id = Db::name('user_pigs')->insertGetId($saveDate);
                //生成订单
                $sellOrder = [];
                $sellOrder['order_no'] = create_trade_no();
                $sellOrder['uid'] = $this->user_id;
                $sellOrder['pig_id'] = $pigInfo['id'];
                $sellOrder['source_price'] = $data['data']['number'];
                $sellOrder['price'] = $data['data']['number'];
                $sellOrder['pig_name'] = $pigInfo['name'];
                $sellOrder['create_time'] = time();
                $sellOrder['sell_id'] = 0;
                $order_id = Db::name('PigOrder')->insertGetId($sellOrder);
                if ($sell_id && $order_id) {
                    //更新用户猪对应的订单号
                    Db::name('user_pigs')->where('id',$sell_id)->update(['order_id'=>$order_id,'end_time'=>time()]);
                    //推广收益减少记录
                    moneyLog($this->user_id,$this->user_id,'share_integral',-$saveDate['price'],2,'售出推广收益');
                    $this->success('出售成功');
                } else {
                    $this->error('出售失败');
                }
            } else {
                $this->error('出售失败,库存不足');
            }

        }
        $piglist = model('Pig')->where('selled_stock < max_stock')->select();
        return view()->assign(['piglist'=>$piglist]);
    }

    /**
     * 领养记录
     * @return \think\response\View
     */
    public function adopt_log()
    {
        $uid = $this->user_id;
        $adoptLog = Db::name('pig_order')->where('uid',$uid)->order('id','desc')->select();
        //$user
        foreach ($adoptLog as $key=>$val) {
            $adoptLog[$key]['pig_info'] = Db::name('task_config')->where('id',$val['pig_id'])->find();
            if ($val['status']==3) {
                $adoptLog[$key]['user_pig'] = Db::name('user_pigs')->where('order_id',$val['id'])->find();
            }
        }
        //申诉记录;
        $sslist = Db::name('shensu')->where('uid',$uid)->select();
        //已完成
//        $wcMap = [];
//        $wclist['uid'] = $uid;
//        $wclist['status'] = 2;
//        $wclist = Db::name('pig_info')->where($wcMap)->select();
        return view()->assign(['loglist'=>$adoptLog,'sslist'=>$sslist]);
    }

    /**
     * 转让记录
     * @return \think\response\View
     */
    public function transfer_log()
    {
        $uid = $this->user_id;
        //待转让
        $userPigs = Db::name('pig_order')->where(['uid'=>$this->user_id,'status'=>0])->order('id','desc')->select();
        foreach ($userPigs as $k=>$v) {

            $userPigs[$k]['pig_info'] = Db::name('task_config')->where('id',$v['pig_id'])->find();
        }
//        dump($userPigs);die;
        $transferlog = Db::name('pig_order')->where('sell_id',$uid)->order('id','desc')->select();
        foreach ($transferlog as $key=>$val) {
            $transferlog[$key]['pig_info'] = Db::name('task_config')->where('id',$val['pig_id'])->find();
            $transferlog[$key]['username'] = Db::name('user')->where('id',$val['uid'])->value('mobile');
        }
        //申诉记录;
        $sslist = Db::name('shensu')->where('uid',$uid)->select();
        return view()->assign(['tralist'=>$transferlog,'userpigs'=>$userPigs,'sslist'=>$sslist]);
    }

    public function adopt_detail()
    {
        $id = $this->request->param('id');
        //echo $id;
        if ($this->request->isPost()) {
            $data = $this->request->post();
            //dump($data);
            $paypwd = $data['data']['paypwd'];
            if (md5($paypwd.config('salt')) != $this->user['pay_password']) {
                $this->error('二级密码不正确');
            }
            //支付凭证
            $voucher = $data['data']['imgs'];
            if (empty($voucher)) {
                $this->error('请上传支付凭证');
            }
            //改变订单状态
            $re = Db::name('pig_order')
                ->where('id',$data['data']['order_id'])
                ->setField(['status'=>2,'voucher'=>'/'.$voucher]);
            $re ? $this->success('支付成功') : $this->error('支付失败');
        }
        $detail = Db::name('pig_order')->where('id',$id)->find();
        $buyinfo = Db::name('user')->where('id',$detail['uid'])->field('id,nickname,mobile')->find();
        $sellinfo = Db::name('user')->where('id',$detail['sell_id'])->field('id,nickname,mobile')->find();
        $payment = Db::name('user_payment')->where('uid',$detail['sell_id'])->select();
        // dump($detail);
        //dump($payment);
        return view()->assign(['detail'=>$detail,'buyer'=>$buyinfo,'seller'=>$sellinfo,'payment'=>$payment]);
    }

    public function transfer_detail()
    {
        $id = $this->request->param('id');
        //dump($id);
        if ($this->request->isPost()) {
            $data = $this->request->post();
            //dump($data);
            $paypwd = $data['data']['paypwd'];
            if (md5($paypwd.config('salt')) != $this->user['pay_password']) {
                $this->error('二级密码不正确');
            }
//           $re = Db::name('pig_order')
//               ->where('id',$data['data']['order_id'])
//               ->setField('status',2);
//           if($re){
//               $pigInfo = Db::name('task_config')->where('id',$data['data']['pig_id'])->find();
//               //把猪添加到买方
//               $userPig = [];
//               //$order
//               $userPig['uid'] = $data['data']['buyer_id'];
//               $userPig['pig_id'] = $data['data']['pig_id'];
//               $userPig['from_id'] = $data['data']['seller_id'];
//               $userPig['price'] = $data['data']['price'];
//               $userPig['create_time'] = time();
//               $userPig['end_time'] = time()+$pigInfo['cycle']*24*3600;
//               Db::name('user_pigs')->insert($userPig);
//               $this->success('操作成功');
//           } else{
//               $this->error('操作失败');
//           }
            //订单确认
            $re = model('PigOrder')->confirm($data['data']['order_id']);
            $re ? $this->success('操作成功') : $this->error('操作失败');
        }
        $detail = Db::name('pig_order')->where('id',$id)->find();
        $buyinfo = Db::name('user')->where('id',$detail['uid'])->field('id,nickname,mobile')->find();
        $sellinfo = Db::name('user')->where('id',$detail['sell_id'])->field('id,nickname,mobile')->find();
        $payment = Db::name('user_payment')->where('uid',$detail['sell_id'])->select();
        //dump($detail);die;
        return view()->assign(['detail'=>$detail,'buyer'=>$buyinfo,'seller'=>$sellinfo,'payment'=>$payment]);
    }

    /**
     * 申诉
     * @return \think\response\View
     */
    public function appeal()
    {
        $order_id = $this->request->param('id');
        $orderInfo = Db::name('pig_order')->where('id',$order_id)->find();
        $self_id = $this->user_id;
        if ($self_id==$orderInfo['uid']) {
            $user_id = $orderInfo['sell_id'];
        } else {
            $user_id = $orderInfo['uid'];
        }
        $username = Db::name('user')->where('id',$user_id)->value('mobile');
        if ($this->request->isPost()) {
            $data = $this->request->post();
            //dump($data);
            $orderInfo = Db::name('pig_order')->where('order_no',$data['data']['order_id'])->find();
            $smap = [];
            $smap['uid'] = $this->user_id;
            $smap['order_id'] = $orderInfo['id'];
            //dump($smap);die;
            $isShenSu = Db::name('shensu')->where($smap)->find();
            if ($isShenSu) $this->error('此订单已有工单，请勿重复提交');
            $content = $data['data']['remark'];
            if (empty($content)) $this->error('请输入申诉内容');
            $appeal = [];
            $appeal['content'] = $content;
            $appeal['create_time'] = time();
            $appeal['uid'] = $this->user_id;
            $appeal['order_id'] = $data['data']['order_id'];
            $appeal['pig_no'] = $orderInfo['order_no'];
            $appeal['username'] = $data['data']['username'];
            $appeal['price'] = $data['data']['price'];
            $re = Db::name('shensu')->insert($appeal);
            if ($re) {
                //冻结订单
                Db::name('pig_order')->where('id',$data['data']['order_id'])->setField('is_lock',1);
                $this->success('提交成功');

            } else {
                $this->error('提交失败');
            }
        }
        //dump($orderInfo);
        return view()->assign(['order_no'=>$orderInfo['order_no'],'price'=>$orderInfo['price'],'username'=>$username]);
    }

    /**
     * yuyue记录
     * @return \think\response\View
     */
    public function reservation_log()
    {
        $user_id = $this->user_id;
        $loglist = Db::name('yuyue')->where('uid',$user_id)->order('id','desc')->select();
        foreach ($loglist as $key=>$val) {
            $pigInfo = Db::name('task_config')->where('id',$val['pig_id'])->find();
            $loglist[$key]['pig_name'] = $pigInfo['name'];
        }
        return view()->assign('loglist',$loglist);
    }

    /**
     * 安全中心
     * @return \think\response\View
     */
    public function safety_center()
    {
        return view();
    }

    /**
     * 修改密码
     * @return \think\response\View
     */
    public function set_pwd()
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            //dump($data);die;
            //是否当前用户
            if ($data['data']['mobile'] != $this->user['mobile']) $this->error('手机号不正确');
            //验证码检测
            $CheckResult = model('Check')->checkCode($data['data']['mobile'],$data['data']['code']);
            if ($CheckResult['state'] == 0) $this->error($CheckResult['msg']);
            empty($data['data']['new_password']) ? $this->error('请输入密码') : '';
            if ($data['data']['new_password'] != $data['data']['confirm_password']) $this->error('两次密码不一致');
            $res = Db::name('user')->where('id',$this->user_id)->setField('password',md5($data['data']['new_password'].config('salt')));
            $res ? $this->success('修改成功','safety_center') : $this->error('没做任何修改');
        }
        return view();
    }

    /**
     * 修改二级密码
     * @return \think\response\View
     */
    public function set_paypwd()
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            //dump(md5($data['data']['new_password'].config('salt')));
            //dump($data);die;
            //是否当前用户
            if ($data['data']['mobile'] != $this->user['mobile']) $this->error('手机号不正确');
            //验证码检测
            $CheckResult = model('Check')->checkCode($data['data']['mobile'],$data['data']['code']);
            if ($CheckResult['state'] == 0) $this->error($CheckResult['msg']);
            empty($data['data']['new_password']) ? $this->error('请输入密码') : '';
            if ($data['data']['new_password'] != $data['data']['confirm_password']) $this->error('两次密码不一致');
            $res = Db::name('user')->where('id',$this->user_id)->setField('pay_password',md5($data['data']['new_password'].config('salt')));
            $res ? $this->success('修改成功','safety_center') : $this->error('没做任何修改');
        }
        return view();
    }

    /**
     * 银行卡列表
     * @return \think\response\View
     */
    public function bankcard()
    {
        $paymentlist = array();
        $pay1['tpye']  = 3;
        $pay1['name']  = '银行卡支付';
        $pay1['logo']  = '/public/static/index/assets/images/bank.png';

        $pay2['tpye']  = 2;
        $pay2['name']  = '微信支付';
        $pay2['logo']  = '/public/static/index/assets/images/weixinpay.png';

        $pay3['tpye']  = 1;
        $pay3['name']  = '支付宝支付';
        $pay3['logo']  = '/public/static/index/assets/images/alipay.png';

        array_push($paymentlist, $pay1, $pay2, $pay3);

        $map = [];
        $map['uid'] = ['eq',$this->user_id];
        $user_paymentlist = Db::name('user_payment')->where($map)->select();

        foreach ($paymentlist as $key=>&$item) {
            $item['account'] = '';
            $item['id'] = '';
            $item['accountname'] = '';
            $item['icon'] = '/public/static/index/assets/images/icon_add.png';
            $item['icontype'] = "add_payment('" . $item['tpye'] . "')";
            foreach ($user_paymentlist as $ukey=>$uitem) {
                if($uitem['type'] == $item['tpye'] ){
                    $item['account'] = $uitem['account'];
                    $item['accountname'] = $uitem['name'];
                    $item['icon'] = '/public/static/index/assets/images/icon_trash3.png';
                    $item['icontype'] = "del_payment('" . $uitem['id']. "')";
                    $item['id'] = $uitem['id'];
                }
            }
        }


        return view()->assign(['paymentlist'=> $paymentlist]);
    }

    /**
     * 添加支付方式
     * @return \think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function add_payment()
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            //dump($data);die;
            //验证码
            //$checkResult = model('Check')->checkCode($data['data']['mobile'],$data['data']['code']);
            //if ($checkResult['state'] ==0) $this->error('验证码错误');
            $payInfo = [];
            $payInfo['name'] = $data['data']['name'];
            empty($payInfo['name']) ? $this->error('收款人不能为空') :  '';
            $payInfo['mobile'] = $data['data']['mobile'];
            $payInfo['account'] = $data['data']['account'];
            empty($payInfo['account']) ? $this->error('账号不能为空') : '';
            $payInfo['uid'] = $this->user_id;
            $payInfo['create_time'] = time();
            if ($data['data']['c_type'] == '银行卡') {
                $payInfo['bank_name'] = $data['data']['bank_name'];
                empty($payInfo['bank_name']) ? $this->error('请选择开户银行') : '';
                $payInfo['branch_name'] = $data['data']['branch_name'];
                empty($payInfo['branch_name']) ? $this->error('支行不能为空') : '';
                $payInfo['type'] = 3;
            } else {
                empty($data['data']['imgs']) ? $this->error('请上传收款码') : '';
                $payInfo['qrcode_url'] = '/'.$data['data']['imgs'];

                $payInfo['type'] = $data['data']['c_type'] == '支付宝' ? 1 : 2;
            }
            $map = [];
            $map['uid'] = $payInfo['uid'];
            $map['type'] = $payInfo['type'];
            if (Db::name('user_payment')->where($map)->find()) $this->error('已有此类型支付方式');
            $res = Db::name('user_payment')->where('id',$payInfo['uid'])->insert($payInfo);
            $res ? $this->success('添加成功','bankcard') : $this->error('添加失败');

        }
        return view();
    }


    public function edit_payment()
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            //dump($data);
            //$checkResult = model('Check')->checkCode($data['data']['mobile'],$data['data']['code']);
            //if ($checkResult['state'] ==0) $this->error('验证码错误');
            $payInfo = [];
            $payInfo['name'] = $data['data']['name'];
            empty($payInfo['name']) ? $this->error('收款人不能为空') :  '';
            $payInfo['mobile'] = $data['data']['mobile'];
            $payInfo['account'] = $data['data']['account'];
            empty($payInfo['account']) ? $this->error('账号不能为空') : '';
            //$payInfo['uid'] = $this->user_id;
            //$payInfo['create_time'] = time();
            if ($data['data']['c_type'] == '银行卡') {
                $payInfo['bank_name'] = $data['data']['bank_name'];
                empty($payInfo['bank_name']) ? $this->error('请选择开户银行') : '';
                $payInfo['branch_name'] = $data['data']['branch_name'];
                empty($payInfo['branch_name']) ? $this->error('支行不能为空') : '';
                $payInfo['type'] = 3;
            } else {
                empty($data['data']['imgs']) ? $this->error('请上传收款码') : '';
                $payInfo['qrcode_url'] = '/'.$data['data']['imgs'];

                $payInfo['type'] = $data['data']['c_type'] == '支付宝' ? 1 : 2;
            }
            $res = Db::name('user_payment')->where('id',$data['data']['id'])->update($payInfo);
            $res ? $this->success('修改成功') : $this->error('没做任何修改');
        }
        return view();
    }
    public function payment_info()
    {
        $data = $this->request->post();
        $id = $data['data']['id'];
        //echo $id;
        $data = Db::name('user_payment')->where('id',$id)->find();
        switch ($data['type']) {
            case 1:
                $data['pay_name'] = '支付宝';
                break;
            case 2:
                $data['pay_name'] = '微信';
                break;
            case 3:
                $data['pay_name'] = '银行卡';
                break;
        }
        return json($data);
    }

    /**
     * 删除支付方式
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function del_payment()
    {
        $data = $this->request->post();
        $id = $data['data']['id'];
        $res = Db::name('user_payment')->where('id',$id)->delete();
        $res ? $this->success('删除成功') : $this->error('操作失败');
    }


    /**
     * 实名认证
     * @return \think\response\View
     */
    public function authentication()
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();


            if (Db::name('identity_auth')->where('uid',$this->user_id)->find()) {
                $this->error('请不要重复提交');
            }
            $realname = $data['data']['real_name'];
            $idCard = $data['data']['identity'];
            empty($realname) ? $this->error('请输入真实姓名') :'';
            empty($idCard) ? $this->error('请输入身份证号') : '';
            $preg_card='/^[1-9]\d{5}(18|19|([23]\d))\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{3}[0-9Xx]$/';
           if (!preg_match($preg_card,$idCard)) $this->error('请输入正确的身份证号');

            $saveData = [];
            $saveData['realname'] = $realname;
            $saveData['idCard'] = $idCard;
            $saveData['uid'] = $this->user_id;
            $saveData['mobile'] = $this->user['mobile'];
            $saveData['create_time'] = time();
            $res = Db::name('identity_auth')->insert($saveData);
            $res ? $this->success('提交成功，等待系统确认','/index/User/index') : $this->error('提交失败');
        }
        return view();
    }
    public function authentication_success()
    {
        return view();
    }

    public function invitation()
    {
        $url = "http://".$_SERVER['HTTP_HOST'].url('login/register',array('pid'=>$this->user_id));
        if(!cache('tgcode'.$this->user_id) || !file_exists(cache('tgcode'.$this->user_id))){
            Vendor('phpqrcode.phpqrcode');
            //生成二维码图片
            $object = new \QRcode();
            $level=3;
            $size=8;
            $errorCorrectionLevel =intval($level) ;//容错级别
            $matrixPointSize = intval($size);//生成图片大小
            $path = "public/tgcode/";
            // 生成的文件名
            $fileName = $path.$this->user_id.'.png';
            $object->png($url,$fileName, $errorCorrectionLevel, $matrixPointSize, 2);
            cache('tgcode'.$this->user_id,$fileName);
        }
        $userinfo = $this->user;
        $codeurl='http://' . $_SERVER ['HTTP_HOST'].'/'.cache('tgcode'.$this->user_id);
        $config=unserialize(Db::name('system')->where('name','site_config')->value('value'));
        $this->assign('config',$config);
        $this->assign('userinfo',$userinfo);
        $this->assign('url',$url);
        $this->assign('codeurl',$codeurl);
        return $this->fetch();
    }
    public function team()
    {
        $user_id = $this->user_id;
        $team = Db::name('user_relation')->where('pid',$user_id)->select();
        foreach ($team as $key=>$val) {
            $map = [];
            $map['rel'] = ['like','%,'.$val['uid'].'%'];
            $count = Db::name('user_relation')->where($map)->count();
            $team[$key]['cnt'] = $count;
        }
        return view()->assign('team',$team);
    }

    public function system_message()
    {
        $newlist = Db::name('news')->where('cate','in','1,2')->select();
        $this->assign('newslist',$newlist);
        return view();
    }



    public function jihuo()
    {
        $base_config=unserialize(Db::name('system')->where('name', 'base_config')->value('value'));
        if ($base_config['jihuo_weifen'] > 0) {
            $user = Db::name('user')->where('id',$this->user_id)->find();
            if ($base_config['jihuo_weifen'] > $user['pay_points']) {
                return ['code' => 201, 'msg' => '微分不足，请充值！'];
            }else{
                if($user['is_jihuo'] == 1){
                    return ['code' => 200];
                }

                Db::name('money_log')->insert([
                    'user_id' => $this->user_id,
                    'username' => $user['username'],
                    'from_id' => 0,
                    'from_username' => $user['username'],
                    'currency' => 'pay_points',
                    'amount' => '-'.$base_config['jihuo_weifen'],
                    'type' => 99999,
                    'note' => '账户激活',
                    'create_time' => date('Y-m-d H:i:s')
                ]);

                Db::name('user')->where('id',$this->user_id)->update([
                    'is_jihuo' => 1,
                    'pay_points' => $user['pay_points'] - $base_config['jihuo_weifen']
                ]);

                return ['code' => 200];
            }
        }
    }


}
