<?php
class Error
{
    private $error;
    private $info;

    public function __construct($error, $info)
    {
        $this->error = intval($error);
        $this->info = $info;
    }

    public function getError()
    {
        return $this->error;
    }

    public function getMessage() {
		if (!empty($this->info)){
			return $this->info;
		}
		switch ($this->error) {
		case 0:
			$this->info = 'success';break;
		case 99:
			$this->info = '99:system error';break;
		case 101:
			$this->info = '101:file already uploaded';break;
		case 102:
			$this->info = '102:file is being uploaded';break;
		case 103:
			$this->info = '103:file is being transfered';break;
		case 104:
			$this->info = '104:user does not buy the service';break;
		case 105:
			$this->info = '105:lack of user space';break;
		case 131:
			$this->info = '131:user request is not supported';break;
		case 132:
			$this->info = '132:illegal users';break;
		case 133:
			$this->info = '133:parity error';break;
		case 134:
			$this->info = '134:user request can not be resolved or missing fields';break;
		case 135:
			$this->info = '135:file format is not supported';break;
		case 136:
			$this->info = '136:file length long';break;
		case 137:
			$this->info = '137:request timestamp expires';break;
		case 138:
			$this->info = '138:file does not exist';break;
		case 200:
			$this->info = '200:file is being uploaded';break;
		case 210:
			$this->info = '210:file upload success';break;
		case 211:
			$this->info = '211:file transfer success';break;
		case 212:
			$this->info = '212:pass review';break;
		case 214:
			$this->info = '214:cdn publish success';break;
		case 220:
			$this->info = '220:upload failed';break;
		case 221:
			$this->info = '221:transfer failed';break;
		case 222:
			$this->info = '222:review failed';break;
		case 224:
			$this->info = '224:CDN publish failed';break;
		case 230:
			$this->info = '230:time out';break;
		case 91:
			$this->info = '91:set access key first';break;
		case 92:
			$this->info = '92:set secret key first';break;
		default:
			$this->info = 'unknown error '.$this->error;break;
		}
		return $this->info;
	}
}

?>
