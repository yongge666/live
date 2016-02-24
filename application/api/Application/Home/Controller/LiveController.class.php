<?php
/**
 * Created by PhpStorm.
 * User: lee
 * Date: 2016/2/23
 * Time: 14:27
 * Email ${EMAIL}
 */

namespace Home\Controller;
use Think\Controller;

class LiveController extends Controller{
    public function index(){
        echo 'hello';
    }

    public function test(){
        echo 'test';
    }

    public function createLiveChannel(){
        $postDate = I('post.');
        if(!isset($postDate['accesskey'])){
            $this->error('accesskey不能为空');
        }

        if(!isset($postDate['secretkey'])){
            $this->error('secretkey不能为空');
        }
        $accesskey = $postDate['accesskey'] ;
        $secretkey = $postDate['secretkey'];

        $name   = 'test';   //字符型，频道名称
        $ifpublic   = 1;    //数值型，频道属性（0是私有，1是公有）
        $ifrecord   = 1;    //数值型，是否允许录制，0表示不录制，1表示录制，取1时下面的4个参数有效
        $recordname = 'test';   //字符型，录制文件名前缀，可以为空，如果不指定该字段则录制文件以频道名称为前缀
        $sizelimit  = 1048576;  //数值型，录制文件最大字节数，以B为单位，最小不低于10M，最大不超过2G，与timelimit二选一。设置一个，另一个设为0
        $timelimit  = 0;    //数值型，录制文件最大时长，以秒为单位，最小不低于3分钟，最大不超过2小时，与sizelimit二选一。设置一个，另一个设为0
        $recordposition = 0;    //数值型，录制文件存储位置，0表示云托管，1表示点播，默认存储到云托管
        $callback   = null; //字符型，录制完成时的回调地址，可以为空，如果不指定该字段则不回调
        vendor('BFCloud.BFCloud');//导入暴风云直播的SDK
        $obj = new \BFCloud($accesskey, $secretkey );
        list($result, $err) = $obj->createLiveChannel($name, $ifpublic, $ifrecord,
            $recordname, $sizelimit, $timelimit, $recordposition, $callback);
//示例表示创建test频道，该频道直播时会录制文件存储至云托管中，录制文件切片大小为1MB，录制文件前缀为“test”

        if (!empty($err)){
            echo $err->getMessage();
        }else
            echo $result,"\r\n";
    }

}
