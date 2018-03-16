<?php
header("Content-Type:text/html;charset=utf-8");
/**
 *  TODO:文件上传类
 *  Author:Shershon
 *  time:2017-10-28
 *  version:1.0
 */
 
class Upload{
	protected $fileName;
	protected $maxSize;
	protected $allowMime;
	protected $allowExt;
	protected $uploadPath;
	protected $imgFlag;
	protected $fileInfo;
	protected $error;
	protected $ext;
	protected $uniName;
	protected $destination;

	/**
  	 * @param string fileName 图片名
  	 * @param bool imgFlag 图片上传标志
  	 * @param string uploadPath 图片上传路径
  	 * @param int maxSize 允许上传最大值
  	 * @param array allowExt 允许图片后缀
  	 * @param array allowMime 允许图片类型
 	 */
	public function __construct($fileName='myFile', $uploadPath=BASE_URL.'/Finance-Images', $imgFlag=true,  $maxSize=52428800, $allowExt=array('jpeg', 'jpg', 'png', 'gif'), $allowMime=array('image/jpeg', 'image/png', 'image/gif')){
		$this->fileName = $fileName;
		$this->maxSize  = $maxSize;
		$this->allowMime = $allowMime;
		$this->allowExt = $allowExt;
		$this->uploadPath = $uploadPath;
		$this->imgFlag = $imgFlag;
		$this->fileInfo = $_FILES[$this->fileName];
	}

	/**
	 * 检测上传文件是否出错
	 * @return bool
	 */
	protected function checkError($n){
		for($a=0;$a<$n;$a++){
			if($this->fileInfo['error'][$a] > 0){
				switch($this->fileInfo['error'][$a]){
					case 1:
						$this->error = '超过了PHP配置文件中upload_max_filesize选项的值';
						break;
					case 2:
						$this->error = '超过了表单中MAX_FILE_SIZE设置的值';
						break;
					case 3:
						$this->error = '文件部分被上传';
						break;
					case 4:
						$this->error = '没有选择上传文件';
						break;
					case 6:
						$this->error = '没有找到临时目录';
						break;
					case 7:
						$this->error = '文件不可写';
						break;
					case 8:
						$this->error = '由于PHP的扩展程序中断文件上传';
						break;
				}
				return false;
			}
		}
		return true;
	}

	/**
	 * 检测上传文件的大小
	 * @return bool
	 */
	protected function checkSize($n){
		for($b=0;$b<$n;$b++){
			if($this->fileInfo['size'][$b] > $this->maxSize){
				$this->error = '上传文件过大';
				return false;
			}
		}
		return true;
	}

	/**
	 * 检测上传文件的扩展名
	 * @return bool
	 */
	protected function checkExt($n){
		for($c=0;$c<$n;$c++){
			$this->ext = strtolower(pathinfo($this->fileInfo['name'][$c], PATHINFO_EXTENSION));
			if(!in_array($this->ext, $this->allowExt)){
				$this->error = '不允许的扩展名';
				return false;
			}
		}
		return true;
	}

	/**
	 * 检测上传文件的类型
	 * @return bool
	 */
	protected function checkMime($n){
		for($d=0;$d<$n;$d++){
			if(!in_array($this->fileInfo['type'][$d], $this->allowMime)){
				$this->error = '不允许的文件类型';
				return false;
			}
		}
		return true;
	}

	/**
	 * 检测上传文件的真实文件
	 * @return bool
	 */
	protected function checkTrueImg($n){
		if($this->imgFlag){
			for($e=0;$e<$n;$e++){
				if(!@getimagesize($this->fileInfo['tmp_name'][$e])){
					$this->error = '不是真实图片';
					return false;
				}
			}
			return true;
		}	
	}

	/**
	 * 检测是否通过HTTP_POST方式上传
	 * @return bool
	 */
	protected function checkHTTPPost($n){
		if(!is_uploaded_file($this->fileInfo['tmp_name'][0])){
			$this->error = '文件不是通过HTTP_POST方式上传';
			return false;
		}	
		return true;
	}

	/**
	 * 显示错误
	 */
	protected function showError(){
		exit("<span style='color:red;'>" . $this->error . "</span>");
	}

	/**
	 * 检测目录不存在，则创建
	 */
	protected function checkUploadPath(){
		if(!file_exists($this->uploadPath)){
			mkdir($this->uploadPath, 0777, true);
			//chmod($this->uploadPath, 0777);
		}
	}

	/**
	 * 产生唯一字符串
	 */
	protected function getUniName(){
		return md5(uniqid(microtime(true), true));
	}

	/**
	 * 上传文件
	 * @return string
	 */
	public function uploadFile($n){
		if($this->checkError($n) && $this->checkSize($n) && $this->checkExt($n) && $this->checkMime($n) && $this->checkTrueImg($n) && $this->checkHTTPPost($n)){
			$this->checkUploadPath();
			for($i=0;$i<$n;$i++){
				$this->uniName = $this->getUniName();
				$this->destination = $this->uploadPath . '/' . $this->uniName . '.' . $this->ext;
				//echo $this->destination;exit;
				if(move_uploaded_file($this->fileInfo['tmp_name'][$i], $this->destination)){
					$this->links[] = $this->destination;
				} else{
					//$this->error = '文件移动失败';
					//$this->showError();
					return Response::show(200, "上传失败");
				}
			}
			return $this->links;
		} else{
			//$this->showError();
			return Response::show(200, "上传失败");
		}
	}
}
