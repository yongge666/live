<?php
/**
 * Created by PhpStorm.
 * User: lee
 * Date: 2016/2/24
 * Time: 15:17
 * Email 2550702985@qq.com
 */
namespace Common\Controller;
use Think\Controller;
class BaseApiController extends Controller{
    /**
     * @var array
     */
    protected $ajaxSuccessMsg = array('ret' => 1, 'des' => 'success', 'data' => array());

    /**
     * @var array
     */
    protected $ajaxErrorMsg = array('ret' => -1, 'des' => 'error', 'data' => array());

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * ...
     * @param string $Action
     * @param string|array $args
     * @throws \Exception
     * @return
     */
    public function __call($Action, $args) {
        throw new \Exception('The Action of ' . $Action . ' not exist in ' . __CLASS__ . '!');
    }

    /**
     * operation success
     * @param string $des
     * @param array $data
     * @return json
     */
    public function success($des = '', $data = array(), $retCode = 1) {
        $this->returnMsg($des, $data, $retCode, $msgType = 1);
    }

    /**
     * operation error
     * @param string $des
     * @param array $data
     * @return json
     */
    public function error($des = '', $retCode = -1, $data = array()) {
        $this->returnMsg($des, $data, $retCode, $msgType = 2);
    }

    /**
     * operation error
     * @param string $des
     * @param array $data
     * @return json
     */
    protected function returnMsg($des = '', $data = array(), $retCode = null, $msgType = null, $msgFormat = 'JSON') {
            if ($msgType == 1) {
                $returnMsg = $this->ajaxSuccessMsg;
            } elseif ($msgType == 2) {
                $returnMsg = $this->ajaxErrorMsg;
            } else {
                $returnMsg = $this->ajaxErrorMsg;
            }
            if ($des) {
                $returnMsg['des'] = $des;
            }
            if ($data) {
                $returnMsg['data'] = $data;
            }
            if ($retCode) {
                $returnMsg['ret'] = $retCode;
            }
           /* if (!$msgFormat) {
                $msgFormatSet = C('AJAX_RETURN_MSG_FORMAT');
                if (!empty($msgFormatSet)) {
                    $msgFormat = (string)$msgFormatSet;
                } else {
                    $msgFormat = 'JSON';
                }
            }*/
            switch (strtoupper($msgFormat)) {
                case 'JSON' :
                    header('Content-Type:application/json; charset=utf-8');
                    exit($this->json_encode_ex($returnMsg, JSON_UNESCAPED_UNICODE));
                //...
                default:
                    header('Content-Type:application/json; charset=utf-8');
                    exit($this->json_encode_ex($returnMsg, JSON_UNESCAPED_UNICODE));
            }

    }

    //php5.3以下版本不兼容json_encode的JSON_UNESCAPED_UNICODE参数
    function json_encode_ex( $value)
    {
        if ( version_compare( PHP_VERSION,'5.4.0','<'))
        {
            $str = json_encode( $value);
            $str =  preg_replace_callback(
                "#\\\u([0-9a-f]{4})#i",
                function( $matchs)
                {
                    return  iconv('UCS-2BE', 'UTF-8',  pack('H4',  $matchs[1]));
                },
                $str
            );
            return  $str;
        }
        else
        {
            return json_encode( $value, JSON_UNESCAPED_UNICODE);
        }
    }





}
