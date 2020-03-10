<?php
/*
'''''''''''''''''''''''''''''''''''''''''''''''''''''''''
源码来自九牛网分享 9nw.cc
''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
 */
namespace app\common\model;

use think\Model;
use think\Db;

class UserRelation extends Model
{
   public function createRelation($id,$pusername)
   {
       $data = [];
       $data['uid'] = $id;
       $data['username'] = model('User')->where('id',$id)->value('username');
       if ($pusername != 0) {
           $pinfo = $this->getByUsername($pusername);

           $data['pid'] = $pinfo['uid'];
           $data['pusername'] = $pusername;
           $data['rel'] = ','.$pinfo['uid'].$pinfo['rel'];
           $data['pid2'] = $pinfo['pid'];
           if ($pinfo['pid2']!=null) {
               $data['pid3'] = $pinfo['pid2'];
           }
       } else {
           $data['pid'] = 0;
           $data['rel'] = ",0";
       }
       $data['create_time'] = time();
       //dump($data);die;
       $re = $this->save($data);
       if ($re && $data['pid']!=0) {
           $this->where('uid',$data['pid'])->setInc('cnt',1);
           model('UserLevel')->updateLevel($data['pid']);
       }

   }




}