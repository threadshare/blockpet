<?php
namespace app\common\model;
use think\Db;
use think\Model;
class Check
{
    /**
     * 手机验证码验证
     * @param $mobile
     * @param $code
     * @return array
     */
   public function checkCode($mobile,$code){
		$mscode = Db::name('mscode');
		$rs = array();
		$codeInfo = $mscode->where(array('mobile'=>$mobile))->order('id desc')->find();

        //验证码验证 是否开启验证
//        $codeinfo=Db::name('config')->field('code_state')->find();
//        if($codeinfo['code_state']!=0){
//            $rs['state'] = 1;
//            return $rs;
//        }
       if($codeInfo['code']!=$code){
           $rs['state'] = 0;
           $rs['msg']='验证码有误';
           return $rs;
		}
		if($codeInfo['endtime'] <time()){
			$rs['state'] = 0;
			$rs['msg']='验证码已过期';
			return $rs;
		}
       if($codeInfo['code']==$code){
           $rs['state'] = 1;
           return $rs;
       }
   }
}


?>
