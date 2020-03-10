/**
 * Created by Administrator on 2018/11/9.
 */
(function(win){
    var callMobile = function (handlerInterface, handlerMethod, parameters){
        var dic = {'handlerInterface':handlerInterface,'function':handlerMethod,'parameters': parameters};
        win.webkit.messageHandlers[handlerInterface].postMessage(dic);
    }
    var init = function(){
        if (/(iPhone|iPad|iPod|iOS)/i.test(navigator.userAgent)){
            var instruct = {};
           if ( !win.app || typeof win.app.wxLogin !== 'function') {
                instruct.wxLogin = function () {  //获取微信开放平台code
                    callMobile('wxLogin','wxLoginCallback',{});
                }
            }
             if ( !win.app || typeof win.app.getLocBySDK !== 'function') {
                instruct.getLocBySDK = function () {  //获取百度定位经纬度
                    callMobile('getLocBySDK','GetLocCallBack',{});
                }
            }
            if (!win.app || typeof win.app.wxPay !== 'function') {
                instruct.wxPay = function (payString, url) {  //调起微信支付这个paystring传的是JSON字符串
                    callMobile('wxPay','wxLoginCallback',{payString, url});
                }
            }
            if (!win.app || typeof win.app.AliPay !== 'function') {
                instruct.AliPay = function (payString) {  //调起支付宝支付
                    callMobile('AliPay','aliPayCallback',{payString});
                }
            }

            if (!win.app || typeof win.app.SaveText !== 'function') {
                instruct.SaveText = function (String) {//调起复制文字
                    callMobile('SaveText','saveTextCallback',{String});
                }
            }
            if (!win.app || typeof win.app.SavePhoto !== 'function') {
                instruct.SavePhoto = function (img) {
                    callMobile('SavePhoto','savePhotoCallback',{img});
                }
            }
            if (!win.app || typeof win.app.Photo !== 'function') {
                instruct.Photo = function () {
                    callMobile('Photo','AppReturnBase64Image',{});
                }
            }
            if (!win.app || typeof win.app.shareImageToWechat!== 'function') {
                instruct.shareImageToWechat= function(img,type,link,content,tite){
                    callMobile('shareImageToWechat','',{img,type,link,content,tite});
                }
            }
            win.app = instruct;
        }
    }
    win.init_ISO = init;
})(window);
init_ISO();