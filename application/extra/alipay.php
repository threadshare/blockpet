<?php
/**
 * Created by PhpStorm.
 * Power By Mikkle
 * Email：776329498@qq.com
 * Date: 2017/8/30
 * Time: 9:59
 */
return [
    //应用ID,您的APPID。
    'app_id' => "2018012902105211",

    //商户私钥，您的原始格式RSA私钥
    'merchant_private_key' => "MIIEvAIBADANBgkqhkiG9w0BAQEFAASCBKYwggSiAgEAAoIBAQDGIBmAXdQCPMU03W2jk4zgsMxbI4ScQRnO8UdRf0nYJUK2s8EZIc06iXmUEEnDtEeLgcfaqFLf7d3Q+ncGEQ5JyMywuMnbmRNjCMPx672PpZBIwYnD01jTR/Wk8VBo51kFpf+VJKcVckkUFJu/V48ZHX8nKWWIJBSlY8MEHWfwfoIhf8zysk8hk9HR3muhUzoVgzQxhfVkaFzs3jn44WczEAaWc+AyCA7OjE9cGiePQx8+y7F75zN2KZlNvoW4cOt6qJS5F4mIbalfl6UQJUDestXGznt1tVEOy+ORelL1wyjUi7id+6d3CigFQGpRytvY480P4oIH4EU6BDZefkXBAgMBAAECggEAcgEgi3PyPcNYOt47a4fI5bX2MW9TrFHtlt3RivyysjRmmhj/QQXpQQjAM8nXmExRat4n8RMwdPg7zjeBa7U+wohP+FSsmrcPp8nwbnGXJ2Q+jQ8Ixe0ETLc4i0vsWCDfYdeuJNTta/LSSEK/iC/LAMmvVAqHCPqyAqVQHzsIhyJRZzusm0vNHtgTz7ERpthJqQRq0WvY+9Y7l3gU7L0pLfuYkift3AjXlHlJ4TrjzK4KXJwRkflmxoeyyQDNn7a6HX0CYCX5OTSNsgFDHoFtmAwT7jdBm/AhlcpEEqmOocMwQcsscj1Dpr8XBuay6cail0F/mlkhF3MpZwallV7JbQKBgQDlTOdA75CRJy2d6dHimbuuYlb5sJxUSklbQxFIe0Um520zW5XPdwEXsQ9lblfxMb+2i5kbwY2OM9ulEfswn6XiwohMeQ74Lc8WsT39tkFmKuvpoRjA04+Ec03Wvr8OJSsja5I/yoh1Xdzpavyr1H/kMxqzUP9wQXq2OenHEu3MiwKBgQDdMeqdI8ZYmfzvsM/fBREr2fYo09YHOtxF3ns22YTU3DKyOeE1U9S2flw9DM+H+O+5vIBtUpood46NDPyI2v0Ne7iw66TEmVN3TKMjWBgcfHCNiotnc/QxPX5Skz+NwTgbUQrO37LG+ekqHXQj7t+xh706foi7dmLWYUCyfMIEYwKBgHnBJv389ueMRRWXpWHMPbLv9rzogWnkdCUobJHvGp34vqxJkjeyOftJgiJawAmLX//fCjKJyM9cS+HPtXBxJRVONC9fDIrNUCv9eywvzXQhkFjiuJETBR7QVuGTMUGijNBm+yYyNdkUOQwcSgQ3dp1GYYQbmzcRHcaK485YB7rXAoGAMXLmSyjjP411QTRa1DWyyAXbBPeOgmFDOw9opjNsgZWUc8mCskRCamXHQxKpFnGtTe15HHd55RANOzUrA4FJTwlYmZykYhsQ0Lu+QLmB8mRTWSEe1wrSDWv84ILwk0UdwOWWL/dMZTUbTgH1o87UDUEWKYx+DsKBiAGH0B8Z1JcCgYBtH+zbQ6ruEN/b3MwluGRntNe2q3KaHzBWmFr4+3VmfJHoMjemRO3s1oMdlICv52Rw4rWQOPK2cOsRkNmQN0E+2FMz5tjqUBOb7ohFqaFl8rE2a/z7ZdvgUmTahrS9e0tFYVYSJHdc/AJWcCaXvPIIAhvtt8QNyNfi/hddKQ1biA==",

    //异步通知地址
    'notify_url' => "http://工程公网访问地址/alipay.trade.wap.pay-PHP-UTF-8/notify_url.php",

    //同步跳转
    'return_url' => "http://mitsein.com/alipay.trade.wap.pay-PHP-UTF-8/return_url.php",

    //编码格式
    'charset' => "UTF-8",

    //签名方式
    'sign_type'=>"RSA2",

    //支付宝网关
    'gatewayUrl' => "https://openapi.alipay.com/gateway.do",

    //支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
    'alipay_public_key' => "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAxiAZgF3UAjzFNN1to5OM4LDMWyOEnEEZzvFHUX9J2CVCtrPBGSHNOol5lBBJw7RHi4HH2qhS3+3d0Pp3BhEOScjMsLjJ25kTYwjD8eu9j6WQSMGJw9NY00f1pPFQaOdZBaX/lSSnFXJJFBSbv1ePGR1/JylliCQUpWPDBB1n8H6CIX/M8rJPIZPR0d5roVM6FYM0MYX1ZGhc7N45+OFnMxAGlnPgMggOzoxPXBonj0MfPsuxe+czdimZTb6FuHDreqiUuReJiG2pX5elECVA3rLVxs57dbVRDsvjkXpS9cMo1Iu4nfundwooBUBqUcrb2OPND+KCB+BFOgQ2Xn5FwQIDAQAB",

];