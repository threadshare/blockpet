<?php
/*
'''''''''''''''''''''''''''''''''''''''''''''''''''''''''
源码来自九牛网分享 9nw.cc
''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
 */
namespace app\index\controller;

use app\common\controller\IndexBase;
use think\Db;
use My\DataReturn;

include APP_PATH."../extend/pay/include.php";
class Pay extends IndexBase
{
	public $config;

	public function _initialize()
	{
		parent::_initialize();
		$this->config = [
			// 沙箱模式
		    'debug'       => false,
		    // 签名类型（RSA|RSA2）
		    'sign_type'   => "RSA2",
		    // 应用ID
		    'appid'       => '2019051564607266',
		    // 支付宝公钥(1行填写，特别注意，这里是支付宝公钥，不是应用公钥，最好从开发者中心的网页上去复制)
		    'public_key'  => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAvd3/mlOApJJ7CzHrHR+c7v/RyYC+gi2Ch8GGtxu0LhGE3HO6Gsk9rNNR1ByG9KY0i/NJ06dZu7vb/qkcX+AzJvX0lyi2dxlAsklf0zy0SzMOsbQwHFv+6NpifSgY9N38EHIzPXREybWFW0ExwjKdwFZxYStW0la+wQlz65VafKS9vhTtLpRkYmf1OEDJikWKS7H/xx/zd1JHH3AGBAxUN5b9wqkR+sG+QfwKVCgm138ldZ+EYosVB+40WsEIebAmvQJeFho5FBeCzPZDCQ29VVwAE8hP40ivUqTDCbI18nExjduW5vmq74bLscgM7I18Tt5naJZDybyR7z40dNuUlQIDAQAB',
		    // 支付宝私钥(1行填写)
		    'private_key' => 'MIIEpQIBAAKCAQEAvd3/mlOApJJ7CzHrHR+c7v/RyYC+gi2Ch8GGtxu0LhGE3HO6Gsk9rNNR1ByG9KY0i/NJ06dZu7vb/qkcX+AzJvX0lyi2dxlAsklf0zy0SzMOsbQwHFv+6NpifSgY9N38EHIzPXREybWFW0ExwjKdwFZxYStW0la+wQlz65VafKS9vhTtLpRkYmf1OEDJikWKS7H/xx/zd1JHH3AGBAxUN5b9wqkR+sG+QfwKVCgm138ldZ+EYosVB+40WsEIebAmvQJeFho5FBeCzPZDCQ29VVwAE8hP40ivUqTDCbI18nExjduW5vmq74bLscgM7I18Tt5naJZDybyR7z40dNuUlQIDAQABAoIBAA2r4Y7MOnQfNArvUj2rnBI9i26V/aHEAyUhU0D2FrhGfKmfD6SEHxPFt5utXi5ZlWkKYKOD5ls1QLcKmEdzDlvc0/rabKEQyW7NB8BfG6BakdFL5zbzjNxZdUQL0hg3r8HDZ1sidx2LG4ohnKKeIGvMDafwhfTpWBzwQE+TVouo9QxSK16Xsymh4UuFBepVG6kC3dI6HFYLuoNl/+bVkyYscPsY/eQCi7rDdIlg+FTkcliTcXmVTEfJICBepb1YKJm5s4aKsJ+qg/xhQ2ajSuBCL31yGBbGND608N1mYLIcpAmjnYRgkUZvcFrUWzyfgRIpfDX42fAxNm0ChcI1wmECgYEA/bhdbsoqCWMfQrbzYvGLq/VfhqjP43DuYSsc7z6soRfE2IOYQlNDqE40WRRGfGJekF/zjQTGT6EM4WdLIuLWFKxnUMFS98A4hO+QRvXh21KuIu9ftniNs5pBzPqyTLCV2PEHHiLCQ/3xQXnKIAuJlfamVLe9ck9qckNUljT8z9kCgYEAv5LAdijK0rTMTwuelmy6oN3qYPj+R3VvGPD80S3G4bzWzXFNMNBRdAC8SKq5X+1ax0N4GgeiRrn3oAL86oa+1zYTQPZ7de37+rldGYmPE3SZJ4mNBoSzHb00YnvBW2ykDvtZpN8CvtOZnrXPfBjMCkaA9pCsjl6lTs05YF27sR0CgYEAmaL7iOwIeni4ZEiupvqHTlCeUMeGYz5uSw61TbZRCJeBDm7ZU0hiTtdUYaCicg2LH7fKnlkG0Q8/4noPCIf9hLVFNqRqXjXaw7zhS+b2pj2xztvOxIrJm7lMIRipwUCo7J4/ZiM8KbnrTm+2UqNJ5DJgVCqTIFYwqhJVdUOO1zkCgYEAlZvY3C49P1Jc4DpVzmn5UdocyUCK7GmEqrjA5+dVE0OziNu8CawWgH52jFVv0b9+jnFdYQ6nJ47iGL9cnxc+ALpFTQ1xG4cQqxyJ3YC1EN+VH/BnBzko3Me/Gk5Fkc2FTgHzXzOeZELCDNU1xVdkOX4YBvazG7hEZwcUkPKCuu0CgYEA+b9D9JvIAPfVwis+sDDCf1pV9VBKh15CAVxiIlXJPwvD0v81oTcH62fSrSICBN/YCZLFWQDbiiFsHaS1iml1JO7Vx7ILuew8g8ymI9N3V5U23FpNnboNI1Lbn7G1mcUTs54WTsTWOCxG3X6l+XJn3VjHgpaBc3pWyGf12P91ykM=',
		    // 支付成功通知地址
		    'notify_url'  => '',
		    // 网页支付回跳地址
		    'return_url'  => '',
		];
	}

	public function chongzhi()
	{
		// var_dump( $this->request->action());
		return view();
	}

	public function scs()
	{
		return $this->success('充值成功',url('index/user/blessings_log'));
	}

	public function send()
	{
		$config = $this->config;
		$config['notify_url'] = 'http://'.$_SERVER ['HTTP_HOST'].'/index/pay/notify';
		$config['return_url'] = 'http://'.$_SERVER ['HTTP_HOST'].'/index/user/index.html';
    	try {
    		$order_sn = create_trade_no();
    		$rs = Db::name('user_czorder')->insert([
    			'order_no' => $order_sn,
    			'money' => input('post.money'),
    			'addtime' => date('Y-m-d H:i:s'),
    			'uid' => $this->user_id,
    			'status' => 0
    		]);
    		if(!$rs){
    			exit('系统错误');
    		}
		    // 实例支付对象
		    $pay = \AliPay\Wap::instance($config);
		    // 参考链接：https://docs.open.alipay.com/api_1/alipay.trade.page.pay
		    $result = $pay->apply([
		        'out_trade_no' => $order_sn, // 商户订单号
		        'total_amount' => input('post.money'), // 支付金额
		        'subject'      => '微分充值', // 支付订单描述
		    ]);
		    echo $result;
		} catch (Exception $e) {
			exit('系统错误');
		    echo $e->getMessage();
		}
	}

	public function notify()
	{
		$config = $this->config;
		$config['notify_url'] = 'http://'.$_SERVER ['HTTP_HOST'].'/index/pay/notify';
		$config['return_url'] = 'http://'.$_SERVER ['HTTP_HOST'].'/index/user/index.html';

		// file_put_contents('notify.txt',var_export($_POST,true));
		// $_POST = array (
		//   'gmt_create' => '2020-02-07 20:25:49',
		//   'charset' => 'utf-8',
		//   'seller_email' => '771733941@qq.com',
		//   'subject' => '微分充值',
		//   'sign' => 'G5tqqas96fQ3Ci+bw93ADAq8QYhU0ZEKtB6p1qPNYSY3UKERFdM06jeg03SQldHswo/D994zGDc8EN53rEYJr7Vf+Dqbg2vzunzugrDuLx/GF2hO68jYkcgp3r9SBRr4aIddQBKIZd3vJtLYuqdiSqAogVrleIpgo3MgPb7XYIcW9/SoFSX4mMwHv97foNgoza2lTGJcNEkMS3sRbkI5ip2nYEhC/LuH34X0VMQ4+tXJVG3457GVZ07Mn77PJCiK3jAXq0FvdTeh/KqmQH8ImpS1hdmZXLA9DQMcUBOwgTO2avqlMu8tHDNcbprWRpChCvgXbp+idJRg1GAC60wBbA==',
		//   'buyer_id' => '2088902562932213',
		//   'invoice_amount' => '0.01',
		//   'notify_id' => '2020020700222202550032211433898912',
		//   'fund_bill_list' => '[{"amount":"0.01","fundChannel":"ALIPAYACCOUNT"}]',
		//   'notify_type' => 'trade_status_sync',
		//   'trade_status' => 'TRADE_SUCCESS',
		//   'receipt_amount' => '0.01',
		//   'buyer_pay_amount' => '0.01',
		//   'app_id' => '2019051564607266',
		//   'sign_type' => 'RSA2',
		//   'seller_id' => '2088531051758980',
		//   'gmt_payment' => '2020-02-07 20:25:50',
		//   'notify_time' => '2020-02-07 20:25:50',
		//   'version' => '1.0',
		//   'out_trade_no' => '0x2402240545060075282',
		//   'total_amount' => '0.01',
		//   'trade_no' => '2020020722001432211418057365',
		//   'auth_app_id' => '2019051564607266',
		//   'buyer_logon_id' => '185****7428',
		//   'point_amount' => '0.00',
		// );
		try {
		    // 实例支付对象
		    $pay = \AliPay\Wap::instance($config);
		    $data = $pay->notify();
		    if (@in_array($data['trade_status'], ['TRADE_SUCCESS', 'TRADE_FINISHED'])) {
		        // @todo 更新订单状态，支付完成
		        // file_put_contents('notify.txt', "收到来自支付宝的异步通知\r\n", FILE_APPEND);
		        // file_put_contents('notify.txt', '订单号：' . $data['out_trade_no'] . "\r\n", FILE_APPEND);
		        // file_put_contents('notify.txt', '订单金额：' . $data['total_amount'] . "\r\n\r\n", FILE_APPEND);
		        $order = Db::name('user_czorder')->where('order_no',$data['out_trade_no'])->where('status',0)->find();
		        if ($order) {
		        	Db::name('user_czorder')->where('order_no',$data['out_trade_no'])->update([
		        		'status' => 1
		        	]);
		        	Db::name('money_log')->insert([
		        		'user_id' => $order['uid'],
		        		'username' => Db::name('user')->where('id',$order['uid'])->value('username'),
		        		'from_id' => 0,
		        		'from_username' => Db::name('user')->where('id',$order['uid'])->value('username'),
		        		'currency' => 'pay_points',
		        		'amount' => $order['money'],
		        		'type' => 999, //充值
		        		'note' => '微分充值',
		        		'create_time' => date('Y-m-d H:i:s')
		        	]);
		        	Db::name('user')->where('id',$order['uid'])->setInc('pay_points',$order['money']);
		        }
		    } else {
		        // file_put_contents('notify.txt', "收到异步通知\r\n", FILE_APPEND);
		    }
		} catch (\Exception $e) {
			// file_put_contents('notify.txt', "收到异步通知，进入异常处理！\r\n", FILE_APPEND);
		    // 异常处理
		    echo $e->getMessage();
		}
	}
}

