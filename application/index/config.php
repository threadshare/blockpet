<?php
return [
    // +----------------------------------------------------------------------
    // | 模板设置
    // +----------------------------------------------------------------------

    'template' => [
        // 模板路径
//        'view_path' => './themes/index/',
        'view_path' => './themes/hdindex/',
    ],
           // 视图输出字符串内容替换
    'view_replace_str'      => [
        '__STATIC__' => '/public/static/Index',
        '__IMG__' => '/public/static/Index/images',
        '__JS__'     => '/public/static/Index/js',
        '__CSS__'    => '/public/static/Index/css',
        '__FONTS__'    => '/public/static/Index/fonts',
    ],
];