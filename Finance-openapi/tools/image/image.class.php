<?php
header("Content-Type:text/html;charset=utf-8");
/**
 *  todo:图片处理类
 *  Author:Shershon
 *  time:2017-10-27
 *  version:1.0
 */

class Image{
	private $image;//内存中的图片
	private $info;//图片的基本信息

	/**
	 * todo:打开一张图片，读取到内存中
	 * @param string src 图片路径
	 */
	public function getSize($src){
		$info = getimagesize($src);
		return $info;
	}

	/**
	 * todo:打开一张图片，读取到内存中
	 * @param string src 图片路径
	 */
	public function __construct($src){
		$info = getimagesize($src);
		$this->info = array(
			'width'  => $info[0],
			'height' => $info[1],
			'type'   => image_type_to_extension($info[2], false),
			'mime'	 => $info['mime']
		);
		$fun = "imagecreatefrom{$this->info['type']}";
		$this->image = $fun($src);
	}

	/**
	 * todo:操作图片（压缩）
	 * @param int width 图片宽度
	 * @param int height 图片高度
	 */
	public function thumb($width, $height){
		$image_thumb = imagecreatetruecolor($width, $height);	
		imagecopyresampled($image_thumb, $this->image, 0, 0, 0, 0, $width, $height, $this->info['width'], $this->info['height']);
		imagedestroy($this->image);
		$this->image = $image_thumb;
	}

	/**
	 * todo:操作图片（自动旋转）
	 * @param string src 图片路径
	 */
	public function rotate($src, $exif){
		$image = imagecreatefromstring(file_get_contents($src));
		// $image = imagecreatefromjpeg($src);
		if(!empty($exif['Orientation'])){
		    switch($exif['Orientation']){
		        case 3:
		            $rotate = imagerotate($image, 180, 0);
		            break;
		        case 6:
		            $rotate = imagerotate($image, -90, 0);
		            break;
		        case 8:
		            $rotate = imagerotate($image, 90, 0);
		            break;
		        default:
		        	$rotate = imagerotate($image, 0, 0);
		    }	
		    imagejpeg($rotate, $src);
		}
	}

	/**
	 * todo:操作图片（添加文字水印）
	 * @param string content 文字内容
	 * @param string font_url 字体路径
	 * @param int size 字体大小
	 * @param array color 字体颜色
	 * @param array local 文字相对原图位置
	 * @param int angle 文字角度
	 */
	public function fontMark($content, $font_url, $size, $color, $local, $angle){
		$color = imagecolorallocate($this->image, $color[0], $color[1], $color[2]);
		imagettftext($this->image, $size, $angle, $local['x'], $local['y'], $color, $font_url, $content);
	}

	/**
	 * todo:操作图片（添加图片水印）
	 * @param string water水印图片路径
	 * @param array local_image水印图片相对原始图片位置
	 * @param string filename原始图片路径
	 */
	public function imageMark($filename,$water,$local_image){
		  //获取背景图片的宽度和高度image_type_to_extension($info[2], false)
		  list($b_w,$b_h,$type) = getimagesize($filename);
		  $type = image_type_to_extension($type, false);
		  //获取水印图片的宽度和高度
		  list($w_w,$w_h) = getimagesize($water);
		  //在背景图片中放水印图片的位置随机起始位置
		  $posX = $b_w-119-10;
		  $posY = $b_h-35-10;
		  //创建背景图片的资源
		  $func = "imagecreatefrom{$type}";
		  $back = $func($filename);
		  //创建水印图片的资源
		  $water = imagecreatefrompng($water);
		  //使用imagecopy()函数将水印图片复制到背景图片指定的位置中
		  imagecopy($back, $water, $posX, $posY, 0, 0, $w_w, $w_h);
		  //保存带有水印图片的背景图片
		  imagejpeg($back,$filename);
		  imagedestroy($back);
		  imagedestroy($water);
	}

	/**
	 * todo:在浏览器中输出图片
	 */
	public function show(){
		header("Content-Type:", $this->info['mime']);
		$funs = "image{$this->info['type']}";
		$funs($this->image);
	}

	/**
	 * todo:把图片保存到硬盘中
	 * @param string newName 生成的图片名
	 */
	public function save($newName, $dir){
		$funs = "image{$this->info['type']}";
		$funs($this->image, $this->checkUploadPath($dir) . '/' . $newName);
	}

	/**
	 * todo:销毁图片
	 */
	public function __destroy(){
		imagedestroy($this->image);
	}

	/**
	 * todo:检查路径是否存在，不存在则创建
	 * @param string path 待检查的目录
	 * return string 返回创建后的目录
	 */
	protected function checkUploadPath($path){
		if(!file_exists($path)){
			mkdir($path, 0777, true);
			//chmod($path, 0777);
			return $path;
		}
		return $path;
	}
}