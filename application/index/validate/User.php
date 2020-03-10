<?php
/*
'''''''''''''''''''''''''''''''''''''''''''''''''''''''''
源码来自九牛网分享 9nw.cc
''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
 */
namespace app\index\validate;

use think\Validate;

class User extends Validate
{
    protected $rule = [
        'mobile'           => 'require|number|unique:user|length:11',
        'realname'         => 'require',
        'zfb'              => 'require',
        'password'         => 'require|alphaNum|confirm:confirm_password',
        'confirm_password' => 'confirm:password',
        'pwd_pay'          => 'require|confirm:confirm_pwd_pay',
        'confirm_pwd_pay'  => 'confirm:pwd_pay',
        'pusername'        =>'require|check_pusername:pusername',
        'xy'               =>'require',
        'email'            => 'email',

        'gusername'        =>'require|check_gusername:gusername',
        //'child_type'       =>'require|check_child:child_type',
        'verify'           => 'require|captcha',
        'newpwd'           =>'require|confirm:new1pwd',
        'new1pwd'           =>'require|confirm:newpwd'
    ];

    protected $message = [
        'mobile.require'            => '请输入手机号',
        'mobile.number'            => '手机号格式错误',
        'mobile.length'            => '手机号长度错误',
        'mobile.unique'            =>'手机号已存在',
        'realname.require'         => '请输入姓名',
        'zfb.require'              => '请输入支付宝账号',


        'password.require'         => '密码不能为空',
        //'password.regex'        =>'密码必须8-16位数字和字母组合',|regex:^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{8,16}$
        'password.confirm'         => '两次输入密码不一致',
        'confirm_password.confirm' => '两次输入密码不一致',
        'pwd_pay.require'          => '支付密码不能为空',
        'pwd_pay.confirm'          => '两次支付密码不一致',

        'confirm_pwd_pay.confirm'  => '两次支付密码不一致',
        'pusername.require'        => '请输入推荐人手机号',
        'xy.require'               => '请阅读协议，如无异议请同意',
        'gusername.require'        => '请输入节点人账户',
        'newpwd.require'           => '新密码不能为空',
        'newpwd.confirm'           => '两次密码不一致',
        'new1pwd.confirm'          => '两次密码不一致',

        'email.email'              => '邮箱格式错误',
        'verify.require'           => '请输入验证码',
        'verify.captcha'           => '验证码不正确',
    ];

    protected $scene = [
        'reg'   =>[
            'mobile'           => 'require|unique:user|number|length:11',
           // 'realname'         => 'require',
           // 'zfb'              => 'require',
            'password'         => 'require|confirm:confirm_password',
            'confirm_password' => 'confirm:password',
            //'pwd_pay'          => 'require|confirm:confirm_pwd_pay',
            //'confirm_pwd_pay'  => 'confirm:pwd_pay',

            //'email'            => 'email',
            'pusername'        =>'require|check_pusername:pusername',
        ],
        'login'  =>  [
            'username'         => 'require',
            'password'         => 'require',
             //'verify'           => 'require|captcha'
        ],
        'editPwd'  =>  [
            'password'         => 'require',
            'newpwd'         => 'require|confirm:new1pwd',
            'new1pwd'           => 'require|confirm:newpwd'
        ],
        'editPay' => [
          'pwd_pay' => 'require',
            'newpwd'         => 'require|confirm:new1pwd',
            'new1pwd'           => 'require|confirm:newpwd'
        ],
    ];
    //验证推荐人姓名
    protected function check_pusername($value)
    {
        $model=model('User');

        //第一条数据不做验证
        if(!$model->column('id')){
            return true;
        }
        if(!$value) {
            return '请正确输入推荐人账户';
        }
        $pinfo=$model->getByMobile($value);
        if(!$pinfo ){
            return '推荐人账户错误';
        }
        if ($pinfo['status'] == 0) {
            return '推荐人账号未激活';
        }
        return true;
        
    }

    //验证节点类型
    protected function check_child($value)
    {
        switch ($value) {
            case 'lchild':
                return true;
                break;
            case 'rchild':
                return true;
                break;
            default:
                return '请输入正确的节点类型';
                break;
        }
        
    }
}