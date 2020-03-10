<?php
namespace app\api\controller;

use think\Controller;
use think\Session;

/**
 * 通用上传接口
 * Class Upload
 * @package app\api\controller
 */
class Upload extends Controller
{
    protected function _initialize()
    {
        parent::_initialize();
        if (!Session::has('admin_id')) {
            $result = [
                'error'   => 1,
                'message' => '未登录'
            ];

            return json($result);
        }
    }

    /**
     * 通用图片上传接口
     * @return \think\response\Json
     */
    public function upload()
    {
        $config = [
            'size' => 2097152,
            'ext'  => 'jpg,gif,png,bmp,mp4'
        ];
        //dump($this->request->file());
        $file = $this->request->file('image');
        //dump($file);die;
        $upload_path = str_replace('\\', '/', ROOT_PATH . 'public/uploads');
        $save_path   = '/public/uploads/';
        $info        = $file->validate($config)->move($upload_path);

        if ($info) {
            $result = [
                'error' => 0,
                'url'   => str_replace('\\', '/', $save_path . $info->getSaveName())
            ];
        } else {
            $result = [
                'error'   => 1,
                'message' => $file->getError()
            ];
        }

        return json($result);
    }

    /**
     * base64上传
     */
    public function upload_base64_paycode(){

        $post = request()->param();
        $bg = $post['data']['img'];//获取图片流
//        $save_url = '/uploads/paycode/' . date('Y', time()) . '/' . date('m-d', time());
//        try {
//            $result = upload_base64($bg,$save_url);
//
//        } catch (\Exception $e) {
//            $this->json_return(0,$e->getMessage());
//        }
        $up_dir = 'public/uploads/paycode/';//存放在当前目录的upload文件夹下
        $base64_img = trim($bg);
        if(preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_img, $result)){
            $type = $result[2];
            if(in_array($type,array('pjpeg','jpeg','jpg','gif','bmp','png'))){
                $code = rand(10000,99999);
                $new_file = $up_dir.date('YmdHis').$code.'.'.$type;
                if(file_put_contents($new_file, base64_decode(str_replace($result[1], '', $base64_img)))){
                    $img_path = str_replace('../../..', '', $new_file);
                    //return  $img_path;
                    $this->json_return(200,'上传成功',$img_path);
                }else{
                    //return '图片上传失败';
                    $this->json_return(200,'上传成功',$result);
                }
            }else{
                //文件类型错误
                return '图片上传类型错误';
            }
        }
        $this->json_return(200,'上传成功',$result);
    }
    public function json_return($status, $message = '', $data = array(),$pages=1){
        $return = array();
        $return['status'] = $status;
        $return['message'] = $message;
        $return['data'] = $data;
        if($pages>1)$return['pages'] = $pages;

        echo json_encode($return);
        exit;
    }
    public function returnJson($status, $message = '', $data = array())
    {
        $return = array();
        $return['status'] = $status;
        $return['message'] = $message;
        $return['data'] = $data;
        echo json_encode($return);
        exit;
    }
}