<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:36:"./themes/admin/task\task_config.html";i:1581060100;s:50:"D:\phpStudy\PHPTutorial\WWW\themes\admin\base.html";i:1581060100;}*/ ?>
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
            <li class="layui-this">抢猪列表</li>
            <li class=""><a href="<?php echo url('admin/Task/taskAdd'); ?>">添加</a></li>
        </ul>
        <div class="layui-tab-content">
            <div class="layui-tab-item layui-show">


                <hr>

                <table class="layui-table">
                    <thead>
                    <tr>
                        <th style="width: 30px;">id</th>
                        <th>名称</th>
                        <th>预约积分</th>
                        <th>即抢积分</th>
                        <th>最低价格</th>
                        <th>最高价格</th>
                        <th>领养开始时间</th>
                        <th>领养结束时间</th>
                        <th>周期</th>
                        <th>智能合约收益比例（%）/天</th>
                        <th>可挖PIG</th>
                        <th>可挖DOGE/(%)</th>
                        <th>今日领养状态</th>
                        <th>已放数量</th>
                        <th>最大库存</th>
                        <th>操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if(is_array($taskConfig) || $taskConfig instanceof \think\Collection || $taskConfig instanceof \think\Paginator): if( count($taskConfig)==0 ) : echo "" ;else: foreach($taskConfig as $key=>$vo): ?>
                    <tr>
                        <td><?php echo $vo['id']; ?></td>
                        <td><?php echo $vo['name']; ?></td>
                        <td><?php echo $vo['pay_points']; ?></td>
                        <td><?php echo $vo['qiang_points']; ?></td>
                        <td><?php echo $vo['min_price']; ?></td>
                        <td><?php echo $vo['max_price']; ?></td>
                        <td><?php echo $vo['start_time']; ?></td>
                        <td><?php echo $vo['end_time']; ?></td>
                        <td><?php echo $vo['cycle']; ?></td>
                        <td><?php echo $vo['contract_revenue']; ?></td>
                        <td><?php echo $vo['pig']; ?></td>
                        <td><?php echo $vo['doge']; ?></td>
                        <td><?php echo !empty($vo['is_open'])?'已领养':'待领养'; ?></td>
                        <td><?php echo $vo['selled_stock']; ?></td>
                        <td><?php echo $vo['max_stock']; ?></td>
                        <td>
                            <a href="<?php echo url('admin/Task/taskEdit',['id'=>$vo['id']]); ?>" class="layui-btn-mini layui-btn">编辑</a>
                            <a href="<?php echo url('admin/Task/taskDel',['id'=>$vo['id']]); ?>" class="layui-btn-mini layui-btn layui-btn-danger ajax-delete">删除</a>
                        </td>
                    </tr>
                    <?php endforeach; endif; else: echo "" ;endif; ?>
                    </tbody>
                </table>
                <!--分页-->

            </div>
        </div>
        <div style="display: none" id='hide'>
            <div class="layui-form-item">
                <label class="layui-form-label">类型</label>
                <div class="layui-input-block">
                    <select name="identity" lay-verify="required">
                        <option value="0">普通会员</option>
                        <option value="1">经理</option>
                        <option value="2">高级经理</option>
                        <option value="3">总监</option>
                    </select>
                </div>
            </div>
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
    high_nav("<?php echo url('taskConfig'); ?>");

</script>

</body>
</html>