<?php
namespace My;
/**
 * Created by PhpStorm.
 * User: 农夫
 * Date: 2017/9/12
 * Time: 9:59
 */
class DataReturn
{
    //规格统一 200成功 302重定向 404 找不到 500系统出错
    /**
     * json格式返回
     * @param $status
     * @param string $message
     * @param array $data
     * @return string
     */
    static public function returnJson($status, $message = '', $data = array())
    {
        $return = array();
        $return['status'] = $status;
        $return['message'] = $message;
        $return['data'] = $data;
        echo json_encode($return);
        exit;
    }

    /**
     * base64 转成数据格式
     * @param $string
     * @return array
     */
    static public function baseFormat($string)
    {
        $basedata = explode('&', base64_decode($string));
        $_array = [];
        foreach ($basedata as $key => $val) {
            list($v1, $v2) = explode('=', $val);
            $_array[$v1] = $v2;
        }
        return $_array;
    }
    /**
     * API 新的md5加密方式
     * @param $token
     * @param $open_id
     * @param $base64
     * @return string
     */
    static public function md5encryption($open_id, $token)
    {
        $md5value = 'gxcx8g$45@wtXs*3@1g';
        return md5($md5value . $token . $md5value . $open_id . $md5value);
    }

    /**
     * base json格式返回
     * @param $status
     * @param string $message
     * @param array $data
     */
    static public function returnBase64Json($status, $message = '', $data = array())
    {
        $return = array();
        $return['status'] = $status;
        $return['message'] = $message;
        $return['data'] = base64_encode(json_encode($data));
        echo json_encode($return);
        exit;
    }

    /**
     * 图片转成base64
     * @param $image_url
     * @return string
     */
    static public function imagebase64($image_url)
    {
        $base64_img = base64_encode(file_get_contents($image_url));//将图片转base64编码
        $imgarr = getimagesize($image_url);//取得图片的大小，类型等
        $img_url = "data:{$imgarr['mime']};base64,{$base64_img}";//合成图片的base64编码成
        return $img_url;
    }

    /**
     * 数据格式转换JSION
     * @param $data
     * @return string
     */
    static public function jsonFormat($data)
    {
        //对数组中每个元素递归进行urlencode操作，保护中文字符
        array_walk_recursive($data, 'jsonFormatProtect');
        $data = json_encode($data);//json encode
        $data = urldecode($data);//将urlencode的内容进行urldecode
        //缩进处理
        $ret = '';
        $pos = 0;
        $length = strlen($data);
        $indent = isset($indent) ? $indent : '    ';
        $newline = "\n";
        $prevchar = '';
        $outofquotes = true;
        for ($i = 0; $i <= $length; $i++) {
            $char = substr($data, $i, 1);
            if ($char == '"' && $prevchar != '\\') {
                $outofquotes = !$outofquotes;
            } elseif (($char == '}' || $char == ']') && $outofquotes) {
                $ret .= $newline;
                $pos--;
                for ($j = 0; $j < $pos; $j++) {
                    $ret .= $indent;
                }
            }
            $ret .= $char;
            if (($char == ',' || $char == '{' || $char == '[') && $outofquotes) {
                $ret .= $newline;
                if ($char == '{' || $char == '[') {
                    $pos++;
                }
                for ($j = 0; $j < $pos; $j++) {
                    $ret .= $indent;
                }
            }
            $prevchar = $char;
        }
        return $ret;
    }

    //配合系统所返回的结果集、
    static public function jsonResult($data,$is_encode = false)
    {
        $status = $data['status'] == 1 ? 200 : 0;
        if($is_encode)
            self::returnBase64Json($status,$data['msg'],$data['result']);


        self::returnJson($status,$data['msg'],$data['result']);
    }

}