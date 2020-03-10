<?php
/*
'''''''''''''''''''''''''''''''''''''''''''''''''''''''''
源码来自九牛网分享 9nw.cc
''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
 */
namespace app\admin\validate;

use think\Validate;

class TradeUser extends Validate
{
    protected $rule = [
        'username'         => 'require|unique:trade_user|number|length:11',
        'password'         => 'require|confirm:confirm_password',
        'confirm_password' => 'confirm:password',
        'mobile'           => 'number|length:11',
        'email'            => 'email',
        'alipay'         => 'require',
        'alipay_name'         => 'require',
        'verify'           => 'require|captcha',
    ];

    protected $message = [
        'username.require'         => '请输入用户名',
        'username.unique'          => '用户名已存在',
        'username.number'            => '手机号格式错误',
        'username.length'            => '手机号长度错误',
        'password.require'         => '请输入密码',
        'password.confirm'         => '两次输入密码不一致',
        'confirm_password.confirm' => '两次输入密码不一致',
        'alipay.require'         => '请输入支付宝账号',
        'alipay_name.require'         => '请输入支付宝姓名',
        'mobile.number'            => '手机号格式错误',
        'mobile.length'            => '手机号长度错误',
        'email.email'              => '邮箱格式错误',
        'verify.require'           => '请输入验证码',
        'verify.captcha'           => '验证码不正确',
        'password1.confirm'        =>'交易密码不一致',
        'confirm_password1.confirm'        =>'交易密码不一致'
    ];

    protected $scene = [
        'reg'  =>  [
            'username'         => 'require|unique:trade_user|number|length:11',
            'password'         => 'require|confirm:confirm_password',
            'confirm_password' => 'confirm:password',
            'mobile'           => 'number|length:11',
            'email'            => 'email',
            'alipay'         => 'require',
            'alipay_name'         => 'require',
        ],
        'editpwd' =>[
            'password'         => 'confirm:confirm_password',
            'confirm_password' => 'confirm:password',
            'password1'         => 'confirm:confirm_password1',
            'confirm_password1' => 'confirm:password1',
        ],
    ];
}