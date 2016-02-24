<?php
/**
 * PHP SDK for OpenAPI V1
 *
 * @version 1.1.2
 * @author www.baofengcloud.com
 */
  
/**
* check for cURL support
*/
if (!function_exists('curl_init'))
{
	 echo 'please install cURL extension first'."\r\n";
	 return -1;
}

/**
* check for JSON support
*/
if (!function_exists('json_decode'))
{
	 echo 'Your PHP version do not support Json, please upgrade to version 5.2.x or higher'."\r\n";
	 return -1;
}
 
require("LinkList.php");
require("Error.php");

class BFCloud
{
	public $_access_key;
	public $_secret_key;
	public $_access_url ="http://access.baofengcloud.com/";
	public $_query_url = "http://livequery.baofengcloud.com";
	public $_channelmgr_url = "http://channelmgr.baofengcloud.com/";
	public $_uptype = 0;	//0 full file upload, 1 resume upload
	public $_servicetype = 0; //0 Open service, 1 One stop service
	public $_filekey;
	public $_callback_url;
	public $_segment_length = 4194304;
	public $_valid_time = 3600; // one hour;
	public $_enable_html5 = 0;  // 0, disable; 1, enable
	
	public function __construct($accesskey, $secretkey)
	{
		$this->_access_key = $accesskey;
		$this->_secret_key = $secretkey;
	}
	
	public function init( $servicetype, $filekey="", $callback_url="")
	{
		$this->_servicetype = $servicetype;
		$this->_filekey = $filekey;
		$this->_callback_url = $callback_url;
	}
	
	public function setValidTime($validtime)
	{
		 $this->_valid_time = $validtime;
	}

	public function setEnableHtml5($enableflag)
	{
		 $this->_enable_html5 = $enableflag;
	}

	//----------for vod ---------------------------------------- 
	/**
	 * Upload video to the platform 
	 * @param  $filename 
	 * @param  $filetype = 1 0:private 1:public
	 * @return  array($uploadinfo,$err)  
	 */
	public function upload($filename, $filetype, $uptype)
	{
		if(empty($filename)){
			return array(null, new Error(-1, 'filename can not be empty'));
		}
		if($filetype != 0 && $filetype != 1){
			return array(null, new Error(-1, 'filetype must be 0 or 1'));
		}
		if (!is_numeric($this->_servicetype)){
			return array(null, new Error(-1, 'servicetype can not be empty'));
		}
		$filetype = intval($filetype);
		if (!file_exists($filename)){
			return array(null, new Error(-1, 'file not exist'));
		}
		$filesize = abs(filesize($filename));
		$ns = explode('/', $filename);
		//make upload token
		$deadline = time() + $this->_valid_time; //5 hours
		$data = array('uptype'=>$uptype, 
				'servicetype' => $this->_servicetype,
				'filename' => $ns[count($ns)-1],
				'filesize' => $filesize,
				'filetype' => $filetype,
				'deadline' => $deadline
			);	
			
		if (!empty($this->_filekey)){
			$data['filekey'] = $this->_filekey;
		}
		if (!empty($this->_callback_url)){
			$data['callbackurl'] = $this->_callback_url;
		}
		
		$upload_token = $this->token_encode($data);
		
		try {
			$response = $this->json_curl($this->_access_url."upload",$upload_token);
		}
		catch (Exception $e) {
			return array(null, new Error($e->getCode(), $e->getMessage()));
		}
		
		$uploadinfo = json_decode($response); 
		if (!isset($uploadinfo->{'status'})) {
			return array(null, new Error(-1,null));
		}
		else if($uploadinfo->{'status'} != 0) {
			return array(null, new Error($uploadinfo->{'status'},null));
		}
	
		try {
			if ($uptype == 0) {
				//full file upload
				list($response, $err) = $this->uploadFullFile($uploadinfo->{'url'}, $filename);
			}
			else {
				//resume upload
				list($response, $err) = $this->resumeUploadCurl($uploadinfo->{'url'}, $filename);
			}
		}
		catch (Exception $e) {
			return array(null, new Error($e->getCode(),$e->getMessage()));
		}
		if($err == null)
			return array($response,null);
		return array(null, new Error($err, null));
	}
	/**
	 * query video information 
	 * @param $filename
	 * @param $filesize
	 * @param $filetype = 1 0:private 1:public
	 * @return $status
	 * 0: success
	 * -1: false
	 */
	public function delete($filename)
	{
		if (empty($filename)){
			return -1;
		}
		if (!is_numeric($this->_servicetype)){
			return -1;
		}
		
		//make delete token
		$deadline = time() + $this->_valid_time; //1 hour
		$data = array(
			'filename' => $filename, 
			'servicetype' => $this->_servicetype,
			'deadline' => $deadline
			);
			
		if (!empty($this->_filekey)){
			$data['filekey'] = $this->_filekey;
		}
		if (!empty($this->_callback_url)){
			$data['callbackurl'] = $this->_callback_url;
		}
		$delete_token = $this->token_encode($data);
		
		try {
			$response = $this->json_curl($this->_access_url."delete",$delete_token);	
		}
		catch (Exception $e) {
			return $e->getCode();
		}
		$deleteinfo = json_decode($response);
		if (!isset($deleteinfo->{'status'})) {
			return -1;
		}
		return $deleteinfo->{'status'};
	}
	
	/**
	 * query video information 
	 * @param  $filename 
	 * @return array($fileinfo, $err)
	 */
	public function query($filename)
	{
		if (empty($filename)){
			return array(null, new Error(-1,'filename can not be empty)'));
		}
		
		if (!is_numeric($this->_servicetype)){
			return array(null, new Error(-1, 'servicetype error'));
		}
		
		$data = array('filename' => $filename, 
				'servicetype' => $this->_servicetype
			);
		
		if (!empty($this->_filekey)){
			$data['filekey'] = $this->_filekey;
		}
		
		$query_token = $this->token_encode($data);
		
		try {
			$response = $this->json_curl($this->_access_url."query",$query_token);
		}
		catch (Exception $e) {
			return array(null, new Error($e->getCode(), $e->getMessage()));
		}
		$queryinfo = json_decode($response);
		if (!isset($queryinfo->{'status'})) {
			return array(null, new Error(-1, null));
		}
		if ($queryinfo->{'status'} != 0) {
			return array(null, new Error($queryinfo->{'status'},null));
		}
		return array($response, null);;	
	}
	/**
	 * change video property 
	 * @param  $filename 
	 * $param  $ifpublic
	 * @return $status
	 */
	function change($filename, $ifpublic){
		if (empty($filename) || !isset($ifpublic)){
			return -1;
		}
		$data = array(
			'filename'=>$filename,
			'filetype'=>intval($ifpublic),
			'servicetype'=>$this->_servicetype
		);
		if (!empty($this->_filekey)){
			$data['filekey'] = $this->_filekey;
		}
		$change_token = $this->token_encode($data);

		try {
			$response = $this->json_curl($this->_access_url."changeproperty",$change_token);
		}catch (Exception $e) {
			return $e->getCode();
		}
		$changeinfo = json_decode($response);
		if (!isset($changeinfo->{'status'})) {
			return  -1;
		}
		if ($changeinfo->{'status'} != 0) {
			return $changeinfo->{'status'};
		}
		return 0;
	}	
	/**
	 * get video play url 
	 * @param  $filename 
	 * @param  $userid
	 * @return array($url, $err)
	 */
	function getVideoPlayUrl($filename, $userid, $auto=1)
	{
		if (empty($filename) || !is_numeric($this->_servicetype) ||  $this->_servicetype == 0){
			return array(null, new Error(-1, 'param error'));;
		}
		
		if (empty($userid) || !is_numeric($userid)){
			return array(null, new Error(-1, 'userid can not be empty'));
		}
		
		list($query_ret, $err) = $this->query($filename);
		if ($err != null)	{
			return array(null, $err);
		}

		$query_ret = json_decode($query_ret);
		$play_url = 'http://vod.baofengcloud.com/'.$userid.'/cloud.swf';
		if (isset($query_ret->{'status'}) && $query_ret->{'status'} == 0){
			$url = trim($query_ret->{'url'});
			
			$play_url .= '?'.$url;
			if ($query_ret->{'ifpublic'} != 1){
				$token = $this->createPlayToken(substr($url, -32));

				$play_url .= '&tk='.$token;
			}
			$play_url .= '&auto='.$auto;
			
		}
		return array($play_url,null);;
	}
	/**
	 * get video play url 
	 * @param  $url 
	 * @param  $ifpublic 
	 * @param  $userid
	 * @return array($playurl, $err)
	 */
	function getVideoPlayUrlByUrl($url, $ifpublic, $userid, $auto=1)
	{
		if (empty($url) || !is_numeric($this->_servicetype) ||  $this->_servicetype == 0){
			return array(null, new Error(-1, 'param error'));;
		}
		if ($ifpublic !=0 && $ifpublic !=1){
			return array(null, new Error(-1, 'ifpublic error'));
		}
		if (empty($userid) || !is_numeric($userid)){
			return array(null, new Error(-1, 'userid can not be empty'));
		}
		$url = trim($url);
		$play_url = 'http://vod.baofengcloud.com/'.$userid.'/cloud.swf';
		$play_url .= '?'.$url;
		if ($ifpublic != 1){
			$token = $this->createPlayToken(substr($url, -32));
				$play_url .= '&tk='.$token;
		}
		$play_url .= '&auto='.$auto;
		return array($play_url,null);
	}
	/*
	 * encode token
	 * @param array $data 
	 * @return string $token (json code)
	 */
	public function token_encode($data)
	{
		$jsonstr = json_encode($data);
		$encoded_json = base64_encode($jsonstr);
		$sign = hash_hmac('sha1', $encoded_json, $this->_secret_key, true);
		$encoded_sign = base64_encode($sign);
		$token = $this->_access_key.':'.$encoded_sign.':'.$encoded_json;
		$token = json_encode(array('token' => $token));
		return stripslashes($token);
	}
	/*
	 * createPlayToken 
	 * create the token used by Saas to play private video
	 * @param string $fid 
	 * @return string $response or throw exception
	 */
	public function createPlayToken($fid){
		if (!isset($fid)){
			return null;
		}
		$token = 'id='.$fid.'&deadline='.(time()+$this->_valid_time).'&enablehtml5='.$this->_enable_html5;
		
		$encoded_json = base64_encode($token);
		$sign = hash_hmac('sha1', $encoded_json, $this->_secret_key, true);
		$encoded_sign = base64_encode($sign);
		$token = $this->_access_key.':'.$encoded_sign.':'.$encoded_json;

		return urlencode(stripslashes($token));
	}
	
	/*
	 * post json request
	 * @param string $url 
	 * @param string $json_string (json code)
	 * @return string $response or throw exception
	 */
	function json_curl($url, $json_string)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);		
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $json_string);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'Content-Length: '.strlen($json_string))
		);
		$response = curl_exec($ch);
		if ($response === false) {
			throw new Exception(curl_error($ch), 90);
		}
		else {			
			$httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			if (200 !== $httpStatusCode) {
				throw new Exception($reponse,$httpStatusCode);
			}
		}
		curl_close($ch);
		return $response;
	}
	/*
	 * upload file once
	 * large file will be cut into blocks, the size of the block is limited by $this->_segment_length
	 * @param string $url 
	 * @param string $filelname
	 * @return string $response or throw exception
	 */
	function uploadFullFile($url, $filename)
	{
		if (!file_exists($filename)) {
			return array(null, new Error(-1,'file is not exist'));
		}
		 
		$file = fopen($filename, 'rb');
		$filesize = abs(filesize($filename));
		fclose($file);
		
		if (class_exists('CURLFile')){
            $cfile = new CURLFile($filename);
            $data = array('file'=>$cfile);
		}else{
            $data = array('file'=>'@'.$filename);
		}
		$response;
	
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		
		$response = curl_exec($ch);
		if ($response === false) {
			throw new Exception(curl_error($ch), 90);
		}
		else {			
			$httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			if (200 !== $httpStatusCode) {
				throw new Exception($response,$httpStatusCode);
			}
		}
		curl_close($ch);
		return array($response, null);
	}
	/*
	 * upload file 
	 * large file will be cut into blocks, the size of the block is limited by $this->_segment_length
	 * @param string $url 
	 * @param string $filelname
	 * @return string $response or throw exception
	 */
	function uploadCurl($url, $filename)
	{
		$start = 0;
		$end = 0;
		if (!file_exists($filename)) {
			return array(null, new Error(-1,'file is not exist'));
		}

		$file = fopen($filename, 'rb');
		$filesize = abs(filesize($filename));
		$response;
		while($content = fread($file, $this->_segment_length)) {
			$end = strlen($content);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Content-Length: '.$end,
				'Content-Range: bytes '.$start.'-'.($start+$end-1).'/'.$filesize,
				'Content-MD5: '.md5($content),
				'Content-Type: application/octet-stream')
			);

			$response = curl_exec($ch);
			if ($response === false) {
				throw new Exception(curl_error($ch), 90);
			}
			else {			
				$httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				if (200 !== $httpStatusCode && 206 != $httpStatusCode) {
					throw new Exception($response,$httpStatusCode);
				}
			}
			$start += $end;
			curl_close($ch);
		}
		fclose($file);
		return array($response, null);
	}
	
	function resumeUploadCurl($url, $filename)
	{
		$start = 0;
		$end = 0;
		if (!file_exists($filename)) {
			return array(null, new Error(-1,'file is not exist'));
		}
		
		$filesize = abs(filesize($filename));
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_HEADERFUNCTION,'readHeadRange');
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Length: 0',
			'Content-Range: bytes */'.$filesize)
		);
		
		$response = curl_exec($ch);
		if ($response === false) {
			throw new Exception(curl_error($ch), 90);
		}
		else {			
			$httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			if (404 == $httpStatusCode) {
				$this->uploadCurl($url, $filename);
				return;
			}
			if (308 !== $httpStatusCode) {
				throw new Exception($response,$httpStatusCode);
			}
		}
		curl_close($ch);
		$file = fopen($filename, 'rb');
		Global $upload_range;
		$upload_mgr = new UploadRangeMgr($upload_range, $filesize);
		$resultinfo = '';
		while($content = fread($file, $this->_segment_length)) {
			$len = strlen($content);
			if (!$upload_mgr->delete($start, $start+$len-1)) {
				$start += $len;
				continue;
			}
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Content-Length: '.$len,
				'Content-Range: bytes '.$start.'-'.($start+$len-1).'/'.$filesize,
				'Content-MD5: '.md5($content),
				'Content-Type: application/octet-stream')
			);

			$response = curl_exec($ch);
			if ($response === false) {
				throw new Exception(curl_error($ch), 90);
			}
			else {			
				$httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				if (200 !== $httpStatusCode && 206 != $httpStatusCode) {
					throw new Exception($response,$httpStatusCode);
				}
				if (200 == $httpStatusCode ){
					$resultinfo = $response;
				}
			}
			$start += $len;
			curl_close($ch);
		}
		fclose($file);
		return array($resultinfo, null);
	}

	/**
	* urlsafe_base64_encode
	* @param string $str
	* @return string
	*/
	function urlsafe_base64_encode($str){
		$find = array("+","/");
		$replace = array("-", "_");
		return str_replace($find, $replace, base64_encode($str));
	}
	
	//----------for live service---------------------------------------- 
	/*
	 * create_live_channel 
	 * create live channel
	 * @param string $name
	 * @param numeric $ifpublic
	 * @return array(result, Error)
	 */
	public function createLiveChannel($name, $ifpublic=0, $ifrecord=0, $recordname=null, $sizelimit=0, $timelimit=0, $recordposition=0, $callback=null) {
		if (empty($name) || !is_numeric($ifpublic) || ($ifrecord==1 && $sizelimit==0 && $timelimit==0 ) || !in_array($recordpostion, array(0,1))) {
			return array(null, new Error(-1, 'params error!'));
		}
		
		//make create token
		$deadline = time() + $this->_valid_time; //1 hour
		$data = array(
				'ifpublic'	=> intval($ifpublic),
				'channelname' => trim($name),
				'ifrecord' => intval($ifrecord),
				'deadline' => $deadline
			);
		if ($ifrecord) {
			if (!empty($recordname) && $recordname != '') {
				$data['recordname'] = $recordname;
			}
			if ($sizelimit) {
				$data['sizelimit'] = intval($sizelimit);
			}
			if ($timelimit) {
				$data['timelimit'] = intval($timelimit);
			}
			$data['recordposition'] = intval($recordposition);
			if (!empty($callback) && $callback != '') {
				$data['callback'] = $callback;
			}
		}
		
		$create_token = $this->token_encode($data);
		try {
			$response = $this->json_curl($this->_channelmgr_url ."createchannel",$create_token);
		}catch (Exception $e) {
			return array(null,new Error($e->getCode(), $e->getMessage()));
		}
		return array($response,null);
		
	}
	/*
	 * delete_live_channel
	 * delete live channel
	 * @param string $channelid 
	 * @return array(result, Error)
	 */
	public function deleteLiveChannel($channelid){
		if (empty($channelid) || strlen($channelid) != 32){
			return array(0, new Error(-1, 'params error!'));
		}

		$deadline = time() + $this->_valid_time;
		$data = array('channelid' => $channelid,'deadline' => $deadline);
		$delete_token = $this->token_encode($data);

		try {
			$response = $this->json_curl($this->_channelmgr_url ."deletechannel",$delete_token);
		}catch (Exception $e) {
			return array(null,new Error($e->getCode(), $e->getMessage()));
		}
		return array($response,null);
	}

	/*
	 * create_live_play_token 
	 * create the token used by live to play private video
	 * @param string $id 
	 * @return string $token
	 */
	public function createLivePlayToken($id){
		if (!isset($id)){
			return null;
		}
		$token = $this->_query_url.'/'.$id.'?deadline='.(time()+$this->_valid_time);
		
		$encoded_data = base64_encode($token);
		$sign = hash_hmac('sha1', $encoded_data, $this->_secret_key, true);
		$encoded_sign = base64_encode($sign);
		$token = $this->_access_key.':'.$encoded_sign.':'.$encoded_data;

		return urlencode(stripslashes($token));
	}
}

class UploadRangeMgr
{
	public $_linklist;
	
	function __construct($upload_range, $filesize)
	{
		$this->_linklist = new LinkList();
		if (sizeof($upload_range) == 0) {
			$this->_linklist->add(0, $filesize-1);
			return 0 ;
		}
		$start = 0;
		foreach ($upload_range as $value) {
			if ($start < $value[0]) {
				$this->_linklist->add($start, $value[0]-1);
			}
			if (isset($value[1])) {
				$start = $value[1]+1;
			}
		}
		
		if (!empty($upload_range[sizeof($upload_range)-1][1])) {
			$this->_linklist->add($upload_range[sizeof($upload_range)-1][1]+1, $filesize-1);
		}
		
		$this->_linklist->printList();
		return 0;
	}
	//delete by sequence
	function delete($start, $end) {
		return $this->_linklist->delete($start, $end);
	}
	 
}

function readHeadRange($ch, $str)
{
	$arr = array_map('trim', explode(':', $str));
	$name = $arr[0];
	//list($name, $value) = array_map('trim', array(':', $str, 2));
	$name = strtolower($name);
	if ('range' === $name) {
		if (empty($arr[1])) {
			$upload_range = array();
		}
		else {
			Global $upload_range;
			$upload_range = explode(',',$arr[1]);
			for ($i=0; $i < sizeof($upload_range); ++$i) {
				$upload_range[$i] = explode('-', $upload_range[$i]);
			}
		}
	}
	return strlen($str);
}

$upload_range = array();
 ?>
