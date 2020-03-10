<?php
namespace alipay;

use think\Cache;
use think\Loader;
/* *
 * 功能：支付宝手机网站支付接口(alipay.trade.wap.pay)接口调试入口页面
 * 版本：2.0
 * 修改日期：2016-11-01
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 请确保项目文件有可写权限，不然打印不了日志。
 */

Loader::import('alipay.pay.service.AlipayTradeService');
Loader::import('alipay.pay.buildermodel.AlipayTradeWapPayContentBuilder');

Class Wappay
{
    public static function pay($params)
    {
        // 1.校检参数
        self::checkParams($params);
        //超时时间
        $timeout_express="1m";
        $payRequestBuilder = new \AlipayTradeWapPayContentBuilder();
        //$payRequestBuilder->setBody($body);

        $payRequestBuilder->setSubject($params['subject']); //订单标题
        $payRequestBuilder->setOutTradeNo($params['out_trade_no']); //商户订单号
        $payRequestBuilder->setTotalAmount($params['total_amount']);    //订单总金额
        $payRequestBuilder->setTimeExpress($timeout_express);
        //配置文件
        $config = config('alipay');
        $payResponse = new \AlipayTradeService($config);
        //请求
        $result=$payResponse->wapPay($payRequestBuilder,$config['return_url'],$config['notify_url']);
    }

    /**
     * 校检参数
     */
    private static function checkParams($params)
    {
        if (empty(trim($params['out_trade_no']))) {
            self::processError('商户订单号(out_trade_no)必填');
        }

        if (empty(trim($params['subject']))) {
            self::processError('商品标题(subject)必填');
        }

        if (floatval(trim($params['total_amount'])) <= 0) {
            self::processError('退款金额(total_amount)为大于0的数');
        }
    }

    /**
     * 统一错误处理接口
     * @param  string $msg 错误描述
     */
    private static function processError($msg)
    {
        throw new \think\Exception($msg);
    }

}


