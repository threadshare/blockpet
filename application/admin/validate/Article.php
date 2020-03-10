<?php
/*
'''''''''''''''''''''''''''''''''''''''''''''''''''''''''
源码来自九牛网分享 9nw.cc
''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
 */
namespace app\admin\validate;

use think\Validate;

class Article extends Validate
{
    protected $rule = [
        'cid'   => 'require',
        'title' => 'require',
        'sort'  => 'require|number'
    ];

    protected $message = [
        'cid.require'   => '请选择所属栏目',
        'title.require' => '请输入标题',
        'sort.require'  => '请输入排序',
        'sort.number'   => '排序只能填写数字'
    ];
}