<?php
namespace app\common\model;
use think\Model;
use think\Db;
class Dxb extends Model
{
    public	$username = "6789";//用户注册账号
    public $pwd   =  'bcc720f2981d1a68dbd66ffd67560c37';//md5(用户注册秘密)
    public	$name = "Gotone";//签名

    //常规验证码
    public function commonCode($mobile){
        //是否发送过验证码了
        $check=Db::name('mscode')->where(array('mobile'=>$mobile))->order('id desc')->find();
        // return $check ;
        if ($check){
            if((time() - $check['sendtime']) < 60){
                $data = array(
                    'state'=>0,
                    'msg'=>'操作频繁,稍后再试'
                );
                return $data;
            }
        }
        $username = $this->username; //请用自己的登录名代替
        $pwd = $this->pwd; //请用自己的登录密码代替
        $name = $this->name; //请用自己的签名代替
        $code=rand(111111,999999);
        $text = urlencode("【{$name}】您的验证码是{$code},如非本人操作请忽略");
        $url = "https://api.smsbao.com/sms?u={$username}&p={$pwd}&m={$mobile}&c={$text}";
        $res = $this->cur_query($url);
        if($res ==0){
            unset($data);
            $data['mobile']=$mobile;
            $data['code']=$code;
            $data['sendtime']=time();
            $data['state']=0;
            $data['endtime']=time()+600;
            Db::name('mscode')->insert($data);

            $data = array(
                'state'=>1,
                'msg'=>'发送成功'
            );
            return $data;
        }else{
            $data = array(
                'state'=>0,
                'msg'=>'发送失败'
            );
            return $data;
        }

    }


    public function cur_query($url){
        $ch = curl_init();
        // 2. 设置选项，包括URL
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_HEADER,0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        // 3. 执行并获取HTML文档内容
        $output = curl_exec($ch);
        if($output === FALSE ){
            echo "CURL Error:".curl_error($ch);
        }
        // 4. 释放curl句柄
        curl_close($ch);
        return $output;
    }

}