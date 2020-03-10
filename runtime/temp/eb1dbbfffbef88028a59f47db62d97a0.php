<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:38:"./themes/admin/system\base_config.html";i:1581336036;s:50:"D:\phpStudy\PHPTutorial\WWW\themes\admin\base.html";i:1581060100;}*/ ?>
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
            <li class="layui-this">配置</li>
        </ul>
        <div class="layui-tab-content">
            <div class="layui-tab-item layui-show">
                <form class="layui-form layui-form-pane" action="" method="post">
                    <fieldset class="layui-elem-field layui-field-title" style="margin-top: 50px;">
                      <legend>基础配置</legend>
                    </fieldset>
                    <div class="layui-form-item">


                            <div class="layui-inline">
                                <label class="layui-form-label">一级推广分成(%)</label>
                                <div class="layui-input-inline">
                                    <input type="text" name="firt_parent" placeholder="直推兑换分成" autocomplete="off" class="layui-input"  value="<?php echo (isset($base_config['firt_parent']) && ($base_config['firt_parent'] !== '')?$base_config['firt_parent']:''); ?>">
                                </div>
                            </div>

                    </div>
                    <div class="layui-form-item">


                        <div class="layui-inline">
                            <label class="layui-form-label">二级推广分成(%)</label>
                            <div class="layui-input-inline">
                                <input type="text" name="second_parent" placeholder="直推兑换分成" autocomplete="off" class="layui-input"  value="<?php echo (isset($base_config['second_parent']) && ($base_config['second_parent'] !== '')?$base_config['second_parent']:''); ?>">
                            </div>
                        </div>

                    </div>
                    <div class="layui-form-item">


                        <div class="layui-inline">
                            <label class="layui-form-label">三级推广分成(%)</label>
                            <div class="layui-input-inline">
                                <input type="text" name="third_parent" placeholder="直推兑换分成" autocomplete="off" class="layui-input"  value="<?php echo (isset($base_config['third_parent']) && ($base_config['third_parent'] !== '')?$base_config['third_parent']:''); ?>">
                            </div>
                        </div>

                    </div>

                    <div class="layui-form-item">
                        <div class="layui-inline">
                            <label class="layui-form-label">PIG提币手续费（%）</label>
                            <div class="layui-input-inline">
                                <input type="text" name="pig_sxf" placeholder="交易手续费" autocomplete="off" class="layui-input"  value="<?php echo (isset($base_config['pig_sxf']) && ($base_config['pig_sxf'] !== '')?$base_config['pig_sxf']:''); ?>">
                            </div>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <div class="layui-inline">
                            <label class="layui-form-label">DOGE提币手续费（%）</label>
                            <div class="layui-input-inline">
                                <input type="text" name="doge_sxf" placeholder="交易手续费" autocomplete="off" class="layui-input"  value="<?php echo (isset($base_config['doge_sxf']) && ($base_config['doge_sxf'] !== '')?$base_config['doge_sxf']:''); ?>">
                            </div>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <div class="layui-inline">
                            <label class="layui-form-label">注册赠送微分</label>
                            <div class="layui-input-inline">
                                <input type="text" name="pay_points" placeholder="注册赠送微分" autocomplete="off" class="layui-input"  value="<?php echo (isset($base_config['pay_points']) && ($base_config['pay_points'] !== '')?$base_config['pay_points']:''); ?>">
                            </div>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <div class="layui-inline">
                            <label class="layui-form-label">销毁猪裂变个数</label>
                            <div class="layui-input-inline">
                                <input type="text" name="split_num" placeholder="销毁裂变个数" autocomplete="off" class="layui-input"  value="<?php echo (isset($base_config['split_num']) && ($base_config['split_num'] !== '')?$base_config['split_num']:''); ?>">
                            </div>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <div class="layui-inline">
                            <label class="layui-form-label">推广收益最低出售额</label>
                            <div class="layui-input-inline">
                                <input type="text" name="sell_min" placeholder="推广收益最低出售额" autocomplete="off" class="layui-input"  value="<?php echo (isset($base_config['sell_min']) && ($base_config['sell_min'] !== '')?$base_config['sell_min']:''); ?>">
                            </div>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <div class="layui-inline">
                            <label class="layui-form-label">推广收益出售次数</label>
                            <div class="layui-input-inline">
                                <input type="text" name="sell_num" placeholder="推广收益推广次数" autocomplete="off" class="layui-input"  value="<?php echo (isset($base_config['sell_num']) && ($base_config['sell_num'] !== '')?$base_config['sell_num']:''); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <div class="layui-inline">
                            <label class="layui-form-label">账户激活所需微分</label>
                            <div class="layui-input-inline">
                                <input type="text" name="jihuo_weifen" placeholder="账户激活所需微分" autocomplete="off" class="layui-input"  value="<?php echo (isset($base_config['jihuo_weifen']) && ($base_config['jihuo_weifen'] !== '')?$base_config['jihuo_weifen']:''); ?>">
                            </div>
                        </div>
                    </div>


                    <div class="layui-form-item">
                        <div class="layui-input-block">
                            <button class="layui-btn" lay-submit lay-filter="*">提交</button>
                            <button type="reset" class="layui-btn layui-btn-primary">重置</button>
                        </div>
                    </div>
                </form>
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
    high_nav("<?php echo url('baseConfig'); ?>");
</script>

</body>
</html>