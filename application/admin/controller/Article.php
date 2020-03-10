<?php
/*
'''''''''''''''''''''''''''''''''''''''''''''''''''''''''
author:ming    contactQQ:811627583
''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
 */
namespace app\admin\controller;

use app\common\model\Article as ArticleModel;
use app\common\model\Category as CategoryModel;
use app\common\controller\AdminBase;

/**
 * 文章管理
 * Class Article
 * @package app\admin\controller
 */
class Article extends AdminBase
{
    protected $article_model;
    protected $category_model;

    protected function _initialize()
    {
        parent::_initialize();
        $this->article_model  = new ArticleModel();
        $this->category_model = new CategoryModel();

        $category_level_list = $this->category_model->getLevelList();
        $this->assign('category_level_list', $category_level_list);
    }

    /**
     * 文章管理
     * @param int    $cid     分类ID
     * @param string $keyword 关键词
     * @param int    $page
     * @return mixed
     */
    public function index($cid = 0, $keyword = '', $page = 1)
    {
        $map   = [];
        $field = 'id,title,cid,author,reading,status,publish_time,sort';

        if ($cid > 0) {
            $category_children_ids = $this->category_model->where(['path' => ['like', "%,{$cid},%"]])->column('id');
            $category_children_ids = (!empty($category_children_ids) && is_array($category_children_ids)) ? implode(',', $category_children_ids) . ',' . $cid : $cid;
            $map['cid']            = ['IN', $category_children_ids];
        }

        if (!empty($keyword)) {
            $map['title'] = ['like', "%{$keyword}%"];
        }

        $article_list  = $this->article_model->field($field)->where($map)->order(['publish_time' => 'DESC'])->paginate(15, false, ['page' => $page]);
        $category_list = $this->category_model->column('name', 'id');

        return $this->fetch('index', ['article_list' => $article_list, 'category_list' => $category_list, 'cid' => $cid, 'keyword' => $keyword]);
    }
    /**
     * 编辑文章
     * @param $id
     * @return mixed
     */
    public function edit()
    {
        $id=$this->request->param('id');
        if ($this->request->isPost()) {
            $data            = $this->request->param();
            $validate_result = $this->validate($data, 'Article');

            if ($validate_result !== true) {
                $this->error($validate_result);
            }
            if($id){
                $re_boolean=true;
            }else{
                $re_boolean=false;
            }
            if ($this->article_model->allowField(true)->isUpdate($re_boolean)->save($data) !== false) {
                $this->success('保存成功');
            } else {
                $this->error('保存失败');
            }
            
        }
        if($id){
            $article = $this->article_model->find($id);
        }else{
            $article =null; 
        }
        $this->assign('article',$article);
        return $this->fetch('edit');
    }

    /**
     * 删除文章
     * @param int   $id
     * @param array $ids
     */
    public function delete($id = 0, $ids = [])
    {
        $id = $ids ? $ids : $id;
        if ($id) {
            if ($this->article_model->destroy($id)) {
                $this->success('删除成功');
            } else {
                $this->error('删除失败');
            }
        } else {
            $this->error('请选择需要删除的文章');
        }
    }
    /**
     * 文章审核状态切换
     * @param array  $ids
     * @param string $type 操作类型
     */
    public function toggle($ids = [], $type = '')
    {
        $data   = [];
        $status = $type == 'audit' ? 1 : 0;

        if (!empty($ids)) {
            foreach ($ids as $value) {
                $data[] = ['id' => $value, 'status' => $status];
            }
            if ($this->article_model->saveAll($data)) {
                $this->success('操作成功');
            } else {
                $this->error('操作失败');
            }
        } else {
            $this->error('请选择需要操作的文章');
        }
    }

    /**
     * 栏目管理
     * @return mixed
     */
    public function cate_index()
    {
        return $this->fetch();
    }

    /**
     * 编辑栏目
     * @param $id
     * @param $pid
     * @return mixed
     */
    public function cate_edit()
    {
        $id=$this->request->param('id');
        $pid=$this->request->param('pid');
        if ($this->request->isPost()) {
            $data            = $this->request->param();
            $validate_result = $this->validate($data, 'Category');
            if ($validate_result !== true) {
                $this->error($validate_result);
            }
            if($data['id']){
                $children = $this->category_model->where(['path' => ['like', "%,{$data['id']},%"]])->column('id');
                if (in_array($data['pid'], $children)) {
                    $this->error('不能移动到自己的子分类');
                }
                $re_boolean=true;
            }else{
                $re_boolean=false;
            }
            if ($this->category_model->allowField(true)->isUpdate($re_boolean)->save($data) !== false) {
                $this->success('保存成功',url('cate_index'));
            } else {
                $this->error('保存失败');
            }
        }
        $category = $this->category_model->where(['id'=>$id])->find();
        if($category['pid']){
            $pid=$category['pid'];
        }
        $this->assign('pid',$pid);
        $this->assign('category',$category);
        return $this->fetch();
    }

    public function cate_delete($id)
    {
        $category = $this->category_model->where(['pid' => $id])->find();
        $article  = $this->article_model->where(['cid' => $id])->find();

        if (!empty($category)) {
            $this->error('此分类下存在子分类，不可删除');
        }
        if (!empty($article)) {
            $this->error('此分类下存在文章，不可删除');
        }
        if ($this->category_model->destroy($id)) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }
}