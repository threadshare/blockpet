<?php
/*
'''''''''''''''''''''''''''''''''''''''''''''''''''''''''
源码来自九牛网分享 9nw.cc
''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
 */
namespace app\admin\validate;

use think\Validate;

class User extends Validate
{
    protected $rule = [
        'username'         => 'require',
        'password'         => 'confirm:confirm_password',
        'confirm_password' => 'confirm:password',
        'mobile'           => 'number|length:11',
        'email'            => 'email',
        'pusername'        =>'require|check_pusername:pusername',
    ];

    protected $message = [
        'username.require'         => '请输入用户名',
        'pusername.require'        => '请输入推荐人账户',
        'child_type.require'       => '请输节点类型',
        'realname.unique'          => '用户名已存在',
        'password.confirm'         => '两次输入密码不一致',
        'confirm_password.confirm' => '两次输入密码不一致',
        'mobile.number'            => '手机号格式错误',
        'mobile.length'            => '手机号长度错误',
        'email.email'              => '邮箱格式错误',
    ];

    protected $scene = [
        'edit'  =>  [
            //'realname'         => 'require',
            'password'         => 'confirm:confirm_password',
            'confirm_password' => 'confirm:password',
            'mobile'           => 'number|length:11',
            //'email'            => 'email',
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
            return '请输入推荐人账户';
        }
        $pinfo=$model->getByMobile($value);
        if(!$pinfo ){
            return '推荐人账户错误';
        }
        return true;
        
    }
}