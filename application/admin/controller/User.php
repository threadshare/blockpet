<?php
/*
'''''''''''''''''''''''''''''''''''''''''''''''''''''''''
author:ming    contactQQ:811627583
''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
 */
namespace app\admin\controller;

use app\common\model\User as UserModel;
use app\common\model\TradeUser;
use app\common\controller\AdminBase;
use think\Config;
use think\Db;

/**
 * 用户管理
 * Class AdminUser
 * @package app\admin\controller
 */
class User extends AdminBase
{
    protected $user_model;

    protected function _initialize()
    {
        parent::_initialize();
        $this->user_model = new UserModel();
    }

    /**
     * 用户管理
     * @param string $keyword
     * @param int    $page
     * @return mixed
     */
    public function index($keyword = '', $page = 1)
    {

        $map = [];
        $user_num=[];
        if ($keyword) {
            $map['mobile'] = ['like', "%{$keyword}%"];
        }
        //$user_list = $this->user_model->where($map)->order('id DESC')->paginate(15, false, ['page' => $page]);
        $sortDate = $this->request->param();
        //dump($sortDate);
        if (!empty($sortDate['order_by'])) {
            //dump($sortDate);die;
            $orderby = $sortDate['order_by'];
            $sort = $sortDate['sort'];
            //dump($sort);
        } else {
            $orderby = 'id';
            $sort = 'desc';
        }
        //dump($orderby);
        //dump($sort);

        $user_list=Db::name('user')
            ->where($map)
            ->order($orderby,$sort)
            ->paginate(15,false,['page' => $page])
            ->each(function ($item,$key){

                $item['pusername'] = Db::name('user_relation')->where('uid',$item['id'])->value('pusername');
                $item['firstcnt'] = Db::name('user_relation')->where('pid',$item['id'])->count();
                $item['secondcnt'] = Db::name('user_relation')->where('pid2',$item['id'])->count();
                $item['thirdcnt'] = Db::name('user_relation')->where('pid3',$item['id'])->count();
                return $item;
            });
        $map['status'] = ['eq','1'];
        $user_num['normal'] = Db::name('user')->where($map)->count();
        $map['status'] = ['eq','0'];
        $user_num['frozen'] = Db::name('user')->where($map)->count();
        //echo Db::name('user')->getLastSql();die;
		$task_config = Db::name('task_config')->order('id desc')->select();
		
        return $this->fetch('index', ['user_list' => $user_list, 'keyword' => $keyword,'user_num'=>$user_num, 'task_config' => $task_config]);
    }
	
	//赠送宠物
	public function zensong()
	{
		$pigInfo = model('Pig')->where(['id'=>input('post.task_id')])->find();
		$saveDate = [];
		$saveDate['uid'] = input('post.user_id');
		$saveDate['pig_id'] = $pigInfo['id'];
		$saveDate['pig_name'] = $pigInfo['name'];
		$saveDate['price'] = input('post.money');
		$saveDate['contract_revenue'] = $pigInfo['contract_revenue'];
		$saveDate['cycle'] = $pigInfo['cycle'];
		$saveDate['doge'] = $pigInfo['doge'];
		$saveDate['pig_no'] = create_trade_no();
		$saveDate['status'] = 0;
		$saveDate['create_time'] = time();
		$saveDate['end_time'] = time()+$pigInfo['cycle']*24*3600;
		$sell_id = Db::name('user_pigs')->insertGetId($saveDate);
		
		$sellOrder = [];
		$sellOrder['order_no'] = create_trade_no();
		$sellOrder['uid'] = input('post.user_id');
		$sellOrder['pig_id'] = $pigInfo['id'];
		$sellOrder['source_price'] = input('post.money');
		$sellOrder['price'] = input('post.money');
		$sellOrder['pig_name'] = $pigInfo['name'];
		$sellOrder['create_time'] = time();
		$sellOrder['sell_id'] = 0;
		//$sellOrder['status'] = 3;
		$order_id = Db::name('PigOrder')->insertGetId($sellOrder);
		if ($sell_id && $order_id) {
			//更新用户猪对应的订单号
            Db::name('user_pigs')->where('id',$sell_id)->update(['order_id'=>$order_id]);
		}
		return ['msg' => '赠送成功'];
	}

    /**
     * 添加用户
     * @return mixed
     */
    public function add()
    {
        return $this->fetch();
    }

    /**
     * 保存用户
     */
    public function save()
    {
        if ($this->request->isPost()) {
            //dump($this->request->post());die;
            $data            = $this->request->except(['money','dt_money']);
            $validate_result = $this->validate($data, 'User');
            if ($validate_result !== true) {
                $this->error($validate_result);
            } else {
                if ($this->user_model->reg()) {
                    $this->success('保存成功');
                } else {
                    $this->error('保存失败');
                }
            }
        }
    }

    /**
     * 编辑用户
     * @param $id
     * @return mixed
     */
    public function edit($id)
    {
        $user = $this->user_model->find($id);
       // dump($user);die;
        $levlist = Db::name('user_level')->select();
        return $this->fetch('edit', ['user' => $user,'levlist'=>$levlist]);
    }

    /**
     * 冻结解冻
     */
    public function activate()
    {
        $data = $this->request->param();
        //dump($data);die;
        $id = $this->request->param('id');
        // 更新用户状态
        $res = Db::name('user')->where('id',$id)->setField('status',$data['status']);
        $res ? $this->success('操作成功') : $this->error('操作失败');

    }

    /**
     * 更新用户
     * @param $id
     */
    public function update($id)
    {
        if ($this->request->isPost()) {
            $data            = $this->request->post();
            $validate_result = $this->validate($data, 'User.edit');
            if ($validate_result !== true) {
                $this->error($validate_result);
            } else {
                $user           = $this->user_model->find($id);
//                $user->id       = $id;
                $saveDate = [];
                if (!empty($data['trade_order'])) {
                    //$user->trade_order = $data['trade_order'];
                    $saveDate['trade_order'] = $data['trade_order'];
                }
                if (!empty($data['credit_score'])) {
                    //$user->credit_score = $data['credit_score'];
                    $saveDate['credit_score'] = $data['credit_score'];
                }
                if (!empty($data['ulevel']) && $data['ulevel'] != $user['ulevel']) {
                    //$user->ulevel = $data['ulevel'];
                    $saveDate['ulevel'] = $data['ulevel'];
                }
                //$user->username = $data['username'];
                // $user->mobile   = $data['mobile'];
                // $user->email    = $data['email'];
                //dump(Config::get('salt'));
                if (!empty($data['password']) && !empty($data['confirm_password'])) {
                    //$user->password =md5($data['password'].Config::get('salt'));
                    $saveDate['password'] = md5($data['password'].Config::get('salt'));
                }
                if (!empty($data['pwd_pay']) && !empty($data['confirm_pwd_pay'])) {
                    //$user->pay_password =md5($data['pwd_pay'].Config::get('salt'));
                    $saveDate['pay_password'] = md5($data['pwd_pay'].Config::get('salt'));
                }
                //dump($user);die;
                if (Db::name('user')->where('id',$data['id'])->update($saveDate)) {
                    //echo Db::name('user')->getLastsql();die;
                    $this->success('更新成功');
                } else {
                    $this->error('更新失败');
                }
            }
        }
    }

    /**
     * 删除用户
     * @param $id
     */
    // public function delete($id)
    // {
    //     if ($this->user_model->destroy($id)) {
    //         $this->success('删除成功');
    //     } else {
    //         $this->error('删除失败');
    //     }
    // }
    
     /**
     * 设置用户身份
     * @param $id
     */
    // public function setIdentity(){
    //     $uid=$this->request->param('uid');
    //     if($this->request->isPost()){
    //         $identity=$this->request->param('identity');
    //         $re=Db::name('user')->where('id',$uid)->update(['identity'=>$identity]);
    //         if($re!==false){
    //             $this->success("设置成功",url('index'));
    //         }else{
    //             $this->error('设置失败');
    //         }
    //     }
    //     $this->assign('info',Db::name('user')->where('id',$uid)->find());
    //     return $this->fetch();
    // }
    /**
     * 推荐关系图
     */
    public function tj_net(){
        $pid=$this->request->param('pid')?$this->request->param('pid'):0;
        if($this->request->param('keyword') && !$pid){
            $username=$this->request->param('keyword');
            $pid=Db::name('user_relation')->where('username',$username)->value('pid');
        }
        $map['pid']=$pid;

//        dump($pid);
        //$map['status']=1;
        //$list= $this->user_model->where($map)->field('id,mobile,create_time')->select();
        $list= Db::name('user_relation')->where($map)->field('id,uid,username,create_time')->select();
        //dump($list);
        foreach ($list as $key => $value) {
            $list[$key]['username']='账号:'.$value['username'].'--注册时间:'.date('Y-m-d H:i:s',$value['create_time']);
            if(Db::name('user_relation')->where('pid',$value['uid'])->count()){
                $list[$key]['isParent']=true;
            }else{
                $list[$key]['isParent']=false;
            }
        }
        if($this->request->isPost()){
            //dump($this->request->param('pid'));
            return $list;
        }
        return $this->fetch();
    }

    /**
     * 节点图
     */
    public function jd_net(){
        $gid=$this->request->param('gid')?$this->request->param('gid'):0;
        $ginfo=$this->user_model->get($gid);
        $map['gid']=$gid;
        $map['status']=1;
        $list= $this->user_model->where($map)->field('id,username,create_time')->select();
        foreach ($list as $key => $value) {
            switch ($value['id']) {
                case $ginfo['lchild']:
                    $list[$key]['username']='账号:'.$value['username'].'(左区)';
                    break;
                case $ginfo['rchild']:
                    $list[$key]['username']='账号:'.$value['username'].'(右区)';
                    break;
                default:
                    $list[$key]['username']='账号:'.$value['username'];
                    break;
            }
            if($this->user_model->where('gid',$value['id'])->count()){
                $list[$key]['isParent']=true;
            }else{
                $list[$key]['isParent']=false;
            }
        }
        if($this->request->isPost()){
            return $list;
        }
        $id=$this->request->param('id');
        if($id){
            $info=$this->user_model->where('id',$id)->find();
        }else{
            $info=$this->user_model->where('gid',0)->find();
        }
        $clinfo=$this->user_model->get($info['lchild']);
        $crinfo=$this->user_model->get($info['rchild']);
        $glclinfo=$this->user_model->get($clinfo['lchild']);
        $grclinfo=$this->user_model->get($clinfo['rchild']);
        $glcrinfo=$this->user_model->get($crinfo['lchild']);
        $grcrinfo=$this->user_model->get($crinfo['rchild']);
        $this->assign('info',$info);
        $this->assign('clinfo',$clinfo);
        $this->assign('crinfo',$crinfo);
        $this->assign('glclinfo',$glclinfo);
        $this->assign('grclinfo',$grclinfo);
        $this->assign('glcrinfo',$glcrinfo);
        $this->assign('grcrinfo',$grcrinfo);
        return $this->fetch();
    }

    /**
     * 实名认证列表
     * @return \think\response\View
     */
    public function identityAuth()
    {
        $map = [];
        $keyword = $this->request->param('keyword');
        if ($keyword) $map['mobile|realname'] = $keyword;
        $list = Db::name('identity_auth')->where($map)->order('id','desc')->paginate(15);
        return view()->assign(['list'=>$list,'keyword'=>$keyword]);
    }

    /**
     * 实名认证操作
     */
    public function audit()
    {
        $id = $this->request->param('id');
        $res = Db::name('identity_auth')->where('id',$id)->setField('status',1);
        if ($res) {
            $this->success('操作成功');
        } else {
           $this->error('操作失败');
        }

    }

    public function userbank()
    {
        $uid = $this->request->param('uid');
        //dump($uid);
        $userpayment = Db::name('user_payment')->where('uid',$uid)->select();
        return view()->assign('user_payment',$userpayment);
    }


    /**
     * 数据导出表格
     * @param string $xlsName
     * @param array $xlsCell
     * @param string $xlsModel
     */
    public function daochu($xlsName='', $xlsCell=array(), $xlsModel=''){//导出Excel
        $xlsName  = "User用户数据表";
        $xlsCell  = array(
            array('id','用户编号'),
            array('realname','用户名'),
            array('mobile','手机号'),
            array('addtime','注册时间')
        );
        //$xlsModel = M('user');
        $xlsData  = Db::name('user')->Field('id,realname,mobile,create_time')->select();
        foreach ($xlsData as $k => $v)
        {
            $xlsData[$k]['addtime'] = date("Y-m-d H:i:s", $v['create_time']);
        }
        // dump($xlsData);die;
        model('PhpOffice')->exportExcel($xlsName,$xlsCell,$xlsData);
    }


}