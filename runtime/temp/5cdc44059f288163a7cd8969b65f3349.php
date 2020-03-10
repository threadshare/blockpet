<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:33:"./themes/admin/shop\withdraw.html";i:1581060100;s:50:"D:\phpStudy\PHPTutorial\WWW\themes\admin\base.html";i:1581060100;}*/ ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title><?php echo $meta_title; ?></title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <link rel="stylesheet" href="/public/static/Admin/js/layui/css/layui.css" media="all">
    <link rel="stylesheet" href="/public/static/Admin/css/font-awesome.min.css">
    <!--CSS引用-->
    
    <link rel="stylesheet" href="/public/static/Admin/css/admin.css">
    <!--[if lt IE 9]>
    <script src="/public/static/Admin/css/html5shiv.min.js"></script>
    <script src="/public/static/Admin/css/respond.min.js"></script>
    <![endif]-->
</head>
<body>
<div class="layui-layout layui-layout-admin">
    <!--头部-->
    <div class="layui-header header">
        <a href=""><img class="logo" src="/public/static/Admin/images/admin_logo.png" alt=""></a>
        <ul class="layui-nav" style="position: absolute;top: 0;right: 20px;background: none;">
            <li class="layui-nav-item"><a href="" data-url="<?php echo url('admin/system/clear'); ?>" id="clear-cache">清除缓存</a></li>
            <li class="layui-nav-item">
                <a href="javascript:;"><?php echo session('admin_name'); ?></a>
                <dl class="layui-nav-child"> <!-- 二级菜单 -->
                    <dd><a href="<?php echo url('admin/admin/updatePassword'); ?>">修改密码</a></dd>
                    <dd><a href="<?php echo url('admin/login/logout'); ?>">退出登录</a></dd>
                </dl>
            </li>
        </ul>
    </div>

    <!--侧边栏-->
    <div class="layui-side layui-bg-black">
        <div class="layui-side-scroll">
            <ul class="layui-nav layui-nav-tree">
                <li class="layui-nav-item layui-nav-title"><a>管理菜单</a></li>
                <li class="layui-nav-item">
                    <a href="<?php echo url('admin/index/index'); ?>"><i class="fa fa-home"></i> 网站信息</a>
                </li>
                <?php if(is_array($menu) || $menu instanceof \think\Collection || $menu instanceof \think\Paginator): if( count($menu)==0 ) : echo "" ;else: foreach($menu as $key=>$vo): if(isset($vo['children'])): ?>
                <li class="layui-nav-item">
                    <a href="javascript:;"><i class="<?php echo $vo['icon']; ?>"></i> <?php echo $vo['title']; ?></a>
                    <dl class="layui-nav-child">
                        <?php if(is_array($vo['children']) || $vo['children'] instanceof \think\Collection || $vo['children'] instanceof \think\Paginator): if( count($vo['children'])==0 ) : echo "" ;else: foreach($vo['children'] as $key=>$v): ?>
                        <dd><a href="<?php echo url($v['name']); ?>"> <?php echo $v['title']; ?></a></dd>
                        <?php endforeach; endif; else: echo "" ;endif; ?>
                    </dl>
                </li>
                <?php else: ?>
                <li class="layui-nav-item">
                    <a href="<?php echo url($vo['name']); ?>"><i class="<?php echo $vo['icon']; ?>"></i> <?php echo $vo['title']; ?></a>
                </li>
                <?php endif; endforeach; endif; else: echo "" ;endif; ?>

                <li class="layui-nav-item" style="height: 30px; text-align: center"></li>
            </ul>
        </div>
    </div>

    <!--主体-->
    
<div class="layui-body">
    <!--tab标签-->
    <div class="layui-tab layui-tab-brief">
        <ul class="layui-tab-title">
            <li class="layui-this">提现管理</li>
        </ul>
        <div class="layui-tab-content">

            <form class="layui-form layui-form-pane" action="<?php echo url('withdraw'); ?>" method="get">
                <div class="layui-inline">
                    <label class="layui-form-label">状态</label>
                    <div class="layui-input-inline">
                        <select name="status">
                            <option value="">全部</option>
                            <option value="0">待处理</option>
                            <option value="1">已处理</option>
                        </select>
                    </div>
                </div>
                <div class="layui-inline">
                    <button class="layui-btn">搜索</button>
                </div>
            </form>
            <hr>

            <form action="" method="post" class="ajax-form">
                <div class="layui-tab-item layui-show">
                    <table class="layui-table">
                        <thead>
                        <tr>
                            <th>会员ID</th>
                            <th>账户</th>
                            <th>币种</th>
                            <th>提现金额</th>
                            <th>实付金额</th>
                            <th>手续费</th>


                            <th>提币地址</th>
                            <th>提现时间</th>
                            <th>处理时间</th>
                            <th>状态</th>
                            <th>操作</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): if( count($list)==0 ) : echo "" ;else: foreach($list as $key=>$vo): ?>
                        <tr>
                            <td><?php echo $vo['uid']; ?></td>
                            <td><?php echo $vo['mobile']; ?></td>
                            <td><?php echo strtoupper($vo['currency']); ?></td>
                            <td><?php echo $vo['num']; ?></td>
                            <td><?php echo $vo['realmoney']; ?></td>
                            <td><?php echo $vo['sxf']; ?></td>

                            <td><?php echo $vo['wallet_address']; ?></td>

                            <td><?php echo date('Y-m-d H:i:s',$vo['create_time']); ?></td>
                            <td>
                                <?php if($vo['examine_time'] != null): ?>
                                <?php echo date('Y-m-d H:i:s',$vo['examine_time']); endif; ?>
                            </td>
                            <td>
                            <?php if($vo['state'] == '1'): ?><span class="layui-btn layui-btn-normal layui-btn-mini">已处理</span><?php endif; if($vo['state'] == '0'): ?><span class="layui-btn layui-btn-danger layui-btn-mini">待处理</span><?php endif; ?>
                            </td>
                            <td>
                                <?php if($vo['state'] == '0'): ?><a data-action="<?php echo url('setWithdraw',['id'=>$vo['id']]); ?>" class="layui-btn layui-btn-mini ajax-action">处理</a><?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; endif; else: echo "" ;endif; ?>
                        </tbody>
                    </table>
                    <!--分页-->
                    <?php echo $list->render(); ?>
                </div>
            </form>
        </div>
    </div>
</div>


    <!--底部-->
    <div class="layui-footer footer">
        <div class="layui-main">
            
        </div>
    </div>
</div>

<script>
    // 定义全局JS变量
    var GV = {
        base_url: "/public/static/Admin"
    };
</script>
<!--JS引用-->
<script src="/public/static/Admin/js/jquery.min.js"></script>
<script src="/public/static/Admin/js/layui/lay/dest/layui.all.js"></script>
<script src="/public/static/Admin/js/admin.js"></script>

<!--页面JS脚本-->

<script>
    high_nav("<?php echo url('withdraw'); ?>");
</script>

</body>
</html>