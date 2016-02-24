<?php
require_once('Uploader1.1.3.php');
 
$alltype = array("wmv","avi","rm","ram","rmvb","mpg","mpeg","dat","asf","3gp","mp4","flv","mov","m4v","mkv","f4v","vob","mod","txt");

function PrintUsage(){
	echo 'usage: php UploadExample.php upload [path] [o:filekey] [o:filetype]',"\r\n";
	echo '       php UploadExample.php query [filename] [O:filekey]',"\r\n";
	echo '       php UploadExample.php delete [filename] [o:filekey]',"\r\n";
	echo '       php UploadExample.php play [filename] [o:filekey]',"\r\n";
	echo '       php UploadExample.php change [filename] [o:ifpublic] [o:filekey]',"\r\n";
	echo '       php UploadExample.php createlivechannel [name] [o:ifpublic]',"\r\n";
	echo '       php UploadExample.php deletelivechannel [channelid] ',"\r\n";
	echo 'o: means option',"\r\n";
}


if ($argc <= 1)
{
	PrintUsage();
	return ;

}

$callbackurl = "";
$service = 1;  //0 Open service, 1 One stop service
$accesskey = "";  //user accesskey
$secretkey = "";  //user secretkey
$uptype = 0; //0 full file upload, 1 resume upload
$filekey = "";
$filename;
$filetype = 1 ;  //0:private 1:public
$filesize;
$userid = 0;

switch ($argv[1]) {
case 'upload':
	$path;
	switch ($argc) {
	case 5:
		$filetype = $argv[4];
	case 4:
		$filekey = $argv[3];
	case 3:
		$path = $argv[2];
		break;
	default:
		echo "usage: php UploadExample.php upload [path] [o:filekey] [o:uptype]\r\n";
		exit;
	}
	$obj = new VideoMgr($accesskey, $secretkey);
	$obj->init($service, $filekey, $callbackurl);
	$dir = opendir($path);
	while(($file = readdir($dir))!=false) {
		if ($file != "" && $file != "..") {
			$ns = explode('.', $file);
			$t_filetype = strtolower($ns[count($ns)-1]);
			if (in_array($t_filetype, $alltype)) {
				$filename = substr($path, -1)=='/' ? $path.$file: $path.'/'.$file;
				echo 'upload file ',$filename,"\r\n";
				list($info, $err) = $obj->upload($filename, $filetype, $uptype);
				if ($err != null){
					echo $err->getMessage()."\r\n";
				}else{
					echo $info."\r\n";
				}
			}

		}
	}
	break;
case 'delete':
	switch ($argc) {
		case 4:
			$filekey = $argv[3];
		case 3:
			$filename = $argv[2];
			break;
		default:
			echo "usage: php UploadExample.php delete [filename] [o:filekey]\r\n";
			exit;
	}
	$obj = new VideoMgr($accesskey, $secretkey);
	$obj->init($service, $filekey, $callbackurl);
	
	$ns = explode('/', $filename);
	$filename = $ns[count($ns)-1];
	$status = $obj->delete($filename);
	echo 'delete status ',$status,"\r\n";
	break;
case 'query':
	switch ($argc) {
	case 4:
		$filekey = $argv[3];
	case 3:
		$filename = $argv[2];
		break;
	default:
		echo 'usage: php UploadExample.php query [filename] [O:filekey]\r\n';
		exit;
	}
	$obj = new VideoMgr($accesskey, $secretkey);
	$obj->init($service, $filekey, $callbackurl);
	list($info, $err) = $obj->query($filename);
	if($err != null){
		echo 'query error : ',$err->getMessage(),"\r\n";
	}else{
		echo 'query result : ',$info,"\r\n";
	}
	
	break;

case 'play':
	switch ($argc) {
	case 4:
		$filekey = $argv[3];
	case 3:
		$filename = $argv[2];
		break;
	default:
		echo "       php UploadExample.php play [filename] [o:filekey]\r\n";
		exit;
	}
	$obj = new VideoMgr($accesskey, $secretkey);
	$obj->init($service, $filekey, $callbackurl);
	
	$ns = explode('/', $filename);
	$filename = $ns[count($ns)-1];
	list($url,$err) = $obj->getVideoPlayUrl($filename, $userid);
	if($err != null){
		echo 'error: ',$err->getMessage(),"\r\n";
	}else{
		echo 'play url: ',$url,"\r\n";
	}
	break;
case 'change':
	switch ($argc) {
		case 5:
			$filekey = $argv[4];
		case 4:
			$ifpublic = $argv[3];
		case 3:
			$filename = $argv[2];
			break;
	}
	$obj = new VideoMgr($accesskey, $secretkey);
	$obj->init($service, $filekey, $callbackurl);
	$status = $obj->change($filename, $ifpublic);
	echo 'change status ',$status,"\r\n";
	break;
	case 'createlivechannel':
		$ifpublic = 0;
		$name = null;	
		switch($argc){
		case 4:
			$ifpublic = $argv[3];
		case 3:
			$name = $argv[2];
			break;
		default:
			echo 'error params!';
			exit;
		}
		$obj = new VideoMgr($accesskey, $secretkey);
		list($result, $err) = $obj->createLiveChannel($name, $ifpublic);
		//list($result, $err) = $obj->createLiveChannel($name, $ifpublic, 1, "test", 1048576, 0, 0, null); //直播录制，按视频大小切片，每1MB为一个录制文件，存储在云托管中
		if (!empty($err)){
			echo $err->getMessage();
		}else 
			echo $result,"\r\n";
		break;
	case 'deletelivechannel':
		$channelid = null;
		switch($argc){
			case 3:
				$channelid = $argv[2];
				break;
			default:
				echo 'error params!';
				exit;
		}
		$obj = new VideoMgr($accesskey, $secretkey);
		list($result, $err) = $obj->deleteLiveChannel($channelid);
		if (!empty($err)){
			echo $err->getMessage();
		}else
			echo $result,"\r\n";
		break;
default:
	PrintUsage();
	break;
}
?>
