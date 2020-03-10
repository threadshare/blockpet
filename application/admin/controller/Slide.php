<?php
/*
'''''''''''''''''''''''''''''''''''''''''''''''''''''''''
author:ming    contactQQ:811627583
''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
 */
namespace app\admin\controller;

use app\common\model\SlideCategory as SlideCategoryModel;
use app\common\model\Slide as SlideModel;
use app\common\controller\AdminBase;
use think\Db;

/**
 * 轮播图管理
 * Class Slide
 * @package app\admin\controller
 */
class Slide extends AdminBase
{

    protected function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 轮播图管理
     * @return mixed
     */
    public function index()
    {
        $slide_category_model = new SlideCategoryModel();
        $slide_category_list  = $slide_category_model->column('name', 'id');
        $slide_list           = SlideModel::all();

        return $this->fetch('index', ['slide_list' => $slide_list, 'slide_category_list' => $slide_category_list]);
    }
    /**
     * 编辑轮播图
     * @param $id
     * @return mixed
     */
    public function edit()
    {
        $id=$this->request->param('id');
        if ($this->request->isPost()) {
            $data            = $this->request->param();
            $validate_result = $this->validate($data, 'Slide');
            if ($validate_result !== true) {
                $this->error($validate_result);
            }
            if($id){
                $re_boolean=true;
            }else{
                $re_boolean=false;
            }
            $slide_model = new SlideModel();
            if ($slide_model->allowField(true)->isUpdate($re_boolean)->save($data) !== false) {
                $this->success('保存成功');
            } else {
                $this->error('保存失败');
            }
        }
        $slide_category_list = SlideCategoryModel::all();
        $slide               = SlideModel::get($id);
        return $this->fetch('edit', ['slide' => $slide, 'slide_category_list' => $slide_category_list]);
    }

    /**
     * 删除轮播图
     * @param $id
     */
    public function delete($id)
    {
        if (SlideModel::destroy($id)) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }

    /**
     * 轮播图分类
     * @return mixed
     */
    public function cate_index()
    {
        $slide_category_list = Db::name('slide_category')->select();

        return $this->fetch('', ['slide_category_list' => $slide_category_list]);
    }

    /**
     * 编辑分类
     * @param $id
     * @return mixed
     */
    public function cate_edit()
    {
        $id=$this->request->param('id');
        //dump($id);
        if ($this->request->isPost()) {
            $data = $this->request->post();
            if($id){
                $re_boolean=true;
            }else{
                $re_boolean=false;
            }
            if (model('SlideCategory')->allowField(true)->isUpdate($re_boolean)->save($data) !== false) {
                $this->success('保存成功');
            } else {
                $this->error('保存失败');
            }
        }
        $slide_category = Db::name('slide_category')->where('id',$id)->find();
       // echo Db::name('slide_category')->getLastSql();
        //dump($slide_category);die;
        return $this->fetch('', ['slide_category' => $slide_category]);
    }

    /**
     * 删除分类
     * @param $id
     * @throws \think\Exception
     */
    public function cate_delete($id)
    {
        if (Db::name('slide_category')->delete($id) !== false) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }
}