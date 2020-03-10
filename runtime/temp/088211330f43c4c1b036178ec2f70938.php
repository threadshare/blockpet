<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:37:"./themes/admin/system\siteConfig.html";i:1581060100;s:50:"D:\phpStudy\PHPTutorial\WWW\themes\admin\base.html";i:1581060100;}*/ ?>
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
            <li class="layui-this">站点配置</li>
        </ul>
        <div class="layui-tab-content">
            <div class="layui-tab-item layui-show">
                <form class="layui-form form-container" action="<?php echo url('admin/system/updateSiteConfig'); ?>" method="post">

                    <div class="layui-form-item">
                        <label class="layui-form-label">注册是否需要设置交易密码</label>
                        <div class="layui-input-block">
                            <input type="checkbox" name="site_config[secpass]" lay-skin="switch" lay-text="开启|关闭" <?php if(!(empty($site_config['secpass']) || (($site_config['secpass'] instanceof \think\Collection || $site_config['secpass'] instanceof \think\Paginator ) && $site_config['secpass']->isEmpty()))): ?> checked <?php endif; ?>>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">平台名称</label>
                        <div class="layui-input-block">
                            <input type="text" name="site_config[site_title]" value="<?php echo (isset($site_config['site_title']) && ($site_config['site_title'] !== '')?$site_config['site_title']:''); ?>" placeholder="请输入平台名称" autocomplete="off" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">平台登录logo(158*136)</label>
                        <div class="layui-input-block">
                            <img src="<?php echo (isset($site_config['login_img']) && ($site_config['login_img'] !== '')?$site_config['login_img']:'../../../public/static/Index/assets/images/logo-login.png'); ?>">
                            <input type="hidden" name="site_config[login_img]" value="<?php echo (isset($site_config['login_img']) && ($site_config['login_img'] !== '')?$site_config['login_img']:''); ?>" class="layui-input layui-input-inline">
                            <br/>
                            <input type="file" name="image" class="layui-upload-file">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">平台背景图片(750*1334)</label>
                        <div class="layui-input-block">
                            <img src="<?php echo (isset($site_config['bg_img']) && ($site_config['bg_img'] !== '')?$site_config['bg_img']:'../../../public/static/Index/assets/images/loginbg.png'); ?>" >
                            <input type="hidden" name="site_config[bg_img]" value="<?php echo (isset($site_config['bg_img']) && ($site_config['bg_img'] !== '')?$site_config['bg_img']:''); ?>" class="layui-input layui-input-inline">
                            <br/>
                            <input type="file" name="image" class="layui-upload-file">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">推广海报背景图片(750*1334)</label>
                        <div class="layui-input-block">
                            <img src="<?php echo (isset($site_config['hb_img']) && ($site_config['hb_img'] !== '')?$site_config['hb_img']:'../../../public/static/Index/assets/images/loginbg.png'); ?>" >
                            <input type="hidden" name="site_config[hb_img]" value="<?php echo (isset($site_config['hb_img']) && ($site_config['hb_img'] !== '')?$site_config['hb_img']:''); ?>" class="layui-input layui-input-inline">
                            <br/>
                            <input type="file" name="image" class="layui-upload-file">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">平台首页logo(251*43)</label>
                        <div class="layui-input-block">
                            <img src="<?php echo (isset($site_config['index_img']) && ($site_config['index_img'] !== '')?$site_config['index_img']:'../../../public/static/Index/assets/images/guilogo.png'); ?>" >
                            <input type="hidden" name="site_config[index_img]" value="<?php echo (isset($site_config['index_img']) && ($site_config['index_img'] !== '')?$site_config['index_img']:''); ?>" class="layui-input layui-input-inline">
                            <br/>
                            <input type="file" name="image" class="layui-upload-file">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">首页banner图片(634*165)</label>
                        <div class="layui-input-block">
                            <img src="<?php echo (isset($site_config['banner_img']) && ($site_config['banner_img'] !== '')?$site_config['banner_img']:'../../../public/static/Index/assets/images/banner.png'); ?>" >
                            <input type="hidden" name="site_config[banner_img]" value="<?php echo (isset($site_config['banner_img']) && ($site_config['banner_img'] !== '')?$site_config['banner_img']:''); ?>" class="layui-input layui-input-inline">
                            <br/>
                            <input type="file" name="image" class="layui-upload-file">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">短信开关</label>
                        <div class="layui-input-block">
                            <input type="checkbox" name="site_config[sms_open]" lay-skin="switch" lay-text="开启|关闭" <?php if(!(empty($site_config['sms_open']) || (($site_config['sms_open'] instanceof \think\Collection || $site_config['sms_open'] instanceof \think\Paginator ) && $site_config['sms_open']->isEmpty()))): ?> checked <?php endif; ?>>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">短信签名</label>
                        <div class="layui-input-block">
                            <input type="text" name="site_config[sms_sign]" value="<?php echo (isset($site_config['sms_sign']) && ($site_config['sms_sign'] !== '')?$site_config['sms_sign']:''); ?>" placeholder="请输入短信签名" autocomplete="off" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">短信api_account</label>
                        <div class="layui-input-block">
                            <input type="text" name="site_config[api_account]" value="<?php echo (isset($site_config['api_account']) && ($site_config['api_account'] !== '')?$site_config['api_account']:''); ?>" placeholder="请输入短信api_account" autocomplete="off" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">短信api_password</label>
                        <div class="layui-input-block">
                            <input type="text" name="site_config[api_password]" value="<?php echo (isset($site_config['api_password']) && ($site_config['api_password'] !== '')?$site_config['api_password']:''); ?>" placeholder="请输入短信api_password" autocomplete="off" class="layui-input">
                        </div>
                    </div>


                    <div class="layui-form-item">
                        <label class="layui-form-label">网站开关</label>
                        <div class="layui-input-block">
                            <input type="checkbox" name="site_config[open]" lay-skin="switch" lay-text="开启|关闭" <?php if(!(empty($site_config['open']) || (($site_config['open'] instanceof \think\Collection || $site_config['open'] instanceof \think\Paginator ) && $site_config['open']->isEmpty()))): ?> checked <?php endif; ?>>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">关闭说明</label>
                        <div class="layui-input-block">
                            <textarea name="site_config[content]" placeholder="关闭说明" class="layui-textarea"><?php echo (isset($site_config['content']) && ($site_config['content'] !== '')?$site_config['content']:''); ?></textarea>
                        </div>
                    </div>
                    <!--<div class="layui-form-item">-->
                        <!--<label class="layui-form-label">APP开放时间</label>-->
                        <!--<div class="layui-input-block">-->
                            <!--开始时间:<input name="site_config[start_time]" placeholder="" class="layui-input" style="display: inline-block;width: 40%;" value="<?php echo (isset($site_config['start_time']) && ($site_config['start_time'] !== '')?$site_config['start_time']:''); ?>">-->
                            <!--结束时间:<input name="site_config[close_time]" placeholder="" class="layui-input" style="display: inline-block;width: 40%;" value="<?php echo (isset($site_config['close_time']) && ($site_config['close_time'] !== '')?$site_config['close_time']:''); ?>">-->
                        <!--</div>-->
                    <!--</div>-->
                    <!-- <div class="layui-form-item">
                        <label class="layui-form-label">SEO标题</label>
                        <div class="layui-input-block">
                            <input type="text" name="site_config[seo_title]" value="<?php echo (isset($site_config['seo_title']) && ($site_config['seo_title'] !== '')?$site_config['seo_title']:''); ?>" placeholder="请输入SEO标题" autocomplete="off" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">SEO关键字</label>
                        <div class="layui-input-block">
                            <input type="text" name="site_config[seo_keyword]" value="<?php echo (isset($site_config['seo_keyword']) && ($site_config['seo_keyword'] !== '')?$site_config['seo_keyword']:''); ?>" placeholder="请输入SEO关键字" autocomplete="off" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">SEO说明</label>
                        <div class="layui-input-block">
                            <textarea name="site_config[seo_description]" placeholder="请输入SEO说明" class="layui-textarea"><?php echo (isset($site_config['seo_description']) && ($site_config['seo_description'] !== '')?$site_config['seo_description']:''); ?></textarea>
                        </div>
                    </div> -->

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
    high_nav("<?php echo url('siteConfig'); ?>");
</script>

</body>
</html>