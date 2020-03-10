<?php
/*
'''''''''''''''''''''''''''''''''''''''''''''''''''''''''
author:ming    contactQQ:811627583
''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
 */
namespace app\admin\controller;

use app\common\controller\AdminBase;
use think\Cache;
use think\Db;

/**
 * 系统配置
 * Class System
 * @package app\admin\controller
 */
class System extends AdminBase
{
    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 站点配置
     */
    public function siteConfig()
    {
        $site_config = Db::name('system')->field('value')->where('name', 'site_config')->find();
        $site_config = unserialize($site_config['value']);

        return $this->fetch('siteConfig', ['site_config' => $site_config]);
    }

    /**
     * 更新配置
     */
    public function updateSiteConfig()
    {
        if ($this->request->isPost()) {
            $site_config                = $this->request->post('site_config/a');
            //$site_config['site_tongji'] = htmlspecialchars_decode($site_config['site_tongji']);
            $data['value']              = serialize($site_config);
            if (Db::name('system')->where('name', 'site_config')->update($data) !== false) {
                $this->success('提交成功');
            } else {
                $this->error('提交失败');
            }
        }
    }
    /**
     * 基础参数配置
     */
    public function baseConfig()
    {
        if($this->request->isPost()){
            $data=$this->request->post();
            $value=serialize($data);
            $re=Db::name('system')->where('name', 'base_config')->update(['value'=>$value]);
            if($re){
                $this->success('提交成功');
                exit;
            }else{
                $this->error('提交失败');
            }
        }
        $base_config=unserialize(Db::name('system')->where('name', 'base_config')->value('value'));
        $this->assign('base_config',$base_config);
        return $this->fetch();
    }

    /**
     * 团队级别
     * @return \think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function marketConfig()
    {
        $list = model('MarketLevel')->select();
        $this->assign('list',$list);
        return view();
    }

    /**
     * 添加团队级别
     * @return \think\response\View
     */
    public function marketadd()
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            $res = model('MarketLevel')->save($data);
            $res ? $this->success('操作成功') : $this->error('操作失败');
        }
        return view();
    }

    /**
     * 市场角色修改
     * @return \think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function marketedit()
    {
        $id = $this->request->param('id');
        //dump($id);
        if ($this->request->isPost()) {
            $data = $this->request->post();
            //dump($data);
            $res = model('MarketLevel')->save($data,['id'=>$data['id']]);
            //dump($res);
            $res ? $this->success('操作成功') : $this->error('修改失败');
        }
        $this->assign('confInfo',model('MarketLevel')->find($id));
        return view();
    }

    public function marketDel()
    {
        $id = $this->request->param('id');
        $res = Db::name('market_level')->where('id',$id)->delete();
        $res ? $this->success('操作成功') : $this->error('操作失败');
    }

    /**
     * 用户级别
     * @return \think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function userLevel()
    {
        $list = model('UserLevel')->select();
        $this->assign('list',$list);
        return view();
    }

    /**
     * 添加市场角色
     * @return \think\response\View
     */
    public function leveladd()
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            $res = model('UserLevel')->save($data);
            $res ? $this->success('操作成功') : $this->error('操作失败');
        }
        return view();
    }

    /**
     * 市场角色修改
     * @return \think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function leveledit()
    {
        $id = $this->request->param('id');
        //dump($id);
        if ($this->request->isPost()) {
            $data = $this->request->post();
            //dump($data);
            $res = model('UserLevel')->save($data,['id'=>$data['id']]);
            //dump($res);
            $res ? $this->success('操作成功') : $this->error('修改失败');
        }
        $this->assign('confInfo',model('UserLevel')->find($id));
        return view();
    }

    public function qgPrice()
    {
        if ($this->request->isPost()) {
            $price = $this->request->post('price');
            //echo $price;
            if (!$price || !is_numeric($price) || $price<0) {
                $this->error('请输入正确的币价');
            }
            $res = Db::name('qg_price')->insert(['price'=>$price,'create_time'=>date('Y-m-d H:i:s')]);
            //echo Db::name('kpi_price')->getLastSql();die;
            $res ? $this->success('操作成功') : $this->error('操作失败');
        }
        $nowPrice = Db::name('qg_price')->order('create_time','desc')->value('price');
        return view()->assign('price',$nowPrice);
    }

    /**
     * 任务配置
     * @return \think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
      public function taskConfig()
      {
          $taskConfig = Db::name('task_config')->select();
          return view()->assign('taskConfig',$taskConfig);
      }

    /**
     * 添加任务
     * @return \think\response\View
     */
      public function taskAdd()
      {
          if ($this->request->isPost()) {
              $data = $this->request->post();
              $res = Db::name('task_config')->insert($data);
              $res ? $this->success('添加成功','taskConfig') : $this->error('操作失败');
          }
          return view();
      }

    /**
     * 任务修改
     * @return \think\response\View
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
      public function taskEdit()
      {
          $id = $this->request->param('id');
          $taskInfo = Db::name('task_config')->where('id',$id)->find();
          if ($this->request->isPost()) {
              $data = $this->request->post();
              $res = Db::name('task_config')->where('id',$data['id'])->update($data);
              //echo Db::name('task_config')->getLastSql();die;
              $res ? $this->success('修改成功','taskConfig') : $this->error('操作失败');
          }
          return view()->assign('taskInfo',$taskInfo);
      }
      public function taskDel()
      {
          $id = $this->request->param('id');
          $res = Db::name('task_config')->where('id',$id)->delete();
          $res ? $this->success('操作成功') : $this->error('操作失败');
      }
      public function personsConfig()
      {
          $personsConfig = Db::name('persons_config')->select();
          return view()->assign('personsConfig',$personsConfig);
      }
      public function personsAdd()
      {
          if ($this->request->isPost()) {
              $data = $this->request->post();
              $res = Db::name('persons_config')->insert($data);
              $res ? $this->success('添加成功','personsConfig') : $this->error('操作失败');
          }
          return view();
      }
      public function personsEdit()
      {
          $id = $this->request->param('id');
          $personInfo = Db::name('persons_config')->where('id',$id)->find();
          if ($this->request->isPost()) {
              $data = $this->request->post();
              $res = Db::name('persons_config')->where('id',$id)->update($data);
              $res ? $this->success('修改成功','personsConfig') : $this->error('操作失败');
          }
          return view()->assign('personsInfo',$personInfo);
      }

    /**
     * 商学院列表
     * @return \think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     *
     */
    public function newsList()
    {
        $list = Db::name('news')->field('id,title,cate,create_time')->select();
        return view()->assign('list',$list);
    }

    /**
     * 视频上传
     * @return \think\response\Json|\think\response\View
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function videoUpload()
    {
        if ($this->request->isPost()) {
                $data = $this->request->post();
                $data['create_time'] = time();
                $res = Db::name('news')->insert($data);
                $res ? $this->success('成功') : $this->error('失败');
            }

        return view();
    }

    /**
     * 商学院内容编辑
     * @return \think\response\View
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function newsEdit()
    {
        $id = $this->request->param('id');
        //dump($id);
        if ($this->request->isPost()) {
            $data = $this->request->post();
            //dump($data);die;
            $res = Db::name('news')->where('id',$data['id'])->update($data);
            //dump($res);
            $res ? $this->success('操作成功') : $this->error('修改失败');
        }
        $this->assign('newsInfo',Db::name('news')->where('id',$id)->find());
        return view();
    }
    public function newsDel()
    {
        $id = $this->request->param('id');
        $res = Db::name('news')->where('id',$id)->delete();
        $res ? $this->success('删除成功') : $this->error('操作失败');
    }

    /**
     * 获取文件后缀
     * @param $filename 文件名
     * @return mixed
     */
     public function getExt($filename)
    {
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            return $ext;
    }

    /**
     * 创建文件存储目录
     * @param $path 文件存储位置
     */
    public function creatDir($path)
    {
        $arr = explode('/',$path);
        $dirAll = '';
        $result = FALSE;
        if(count($arr) > 0) {
            foreach($arr as $key=>$value) {
                $tmp = trim($value);
                if($tmp != '') {
                    $dirAll .= $tmp.'/';
                    if(!file_exists($dirAll)) {
                        mkdir($dirAll,0777,true);
                    }
                }
            }
        }
    }

    /**
     * 清除缓存
     */
    public function clear()
    {
        if (delete_dir_file(RUNTIME_PATH)) {
            $this->success('清除缓存成功');
        } else {
            $this->error('清除缓存失败');
        }
    }


}