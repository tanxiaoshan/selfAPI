<?php
require BASE_URL . '/Finance-openapi/tools/image/image.class.php';
require BASE_URL . '/Finance-openapi/tools/upload/upload.class.php';
//echo 123;die;
class UploadController{
	public function image(){//通用图片（投诉[可以多张]、头像[一张]、封面[一张]）
		$file = isset($_FILES['avatar']) ? $_FILES['avatar'] : '';
		$type = isset($_POST['type']) ? $_POST['type'] : '';
		$uid = isset($_POST['uid']) ? $_POST['uid'] : '';

		if(isset($_FILES['avatar'])){
			//将图片上传至服务器
			$day = date('Ymd');
			if($type == 'headImage'){
				$upload= new upload('avatar', BASE_URL.'/Finance-Images/headImage/'.$day.'/'.$uid);
			} elseif($type=='coverImage'){
				$upload= new upload('avatar', BASE_URL.'/Finance-Images/coverImage/'.$day.'/'.$uid);
			} else{
				$upload= new upload('avatar', BASE_URL.'/Finance-Images/complaintImage/'.$day.'/'.$uid);
			}
			$num = count($_FILES['avatar']['name']);
			$dest = $upload->uploadFile($num);
			//保存原始图片拍照方向信息，压缩之后，进行旋转（需要旋转的）
			for($i=0,$len=count($dest);$i<$len;$i++){
				$src = $dest[$i];
				$dir = dirname($src);
				$dir2 = str_replace('/FinanceDisk/Finance-Images', IMG_URL, $dir);
				$image = new Image($src);

				//处理图片前保存拍照方向参数
				$exif = @exif_read_data($src);

				$info = $image->getSize($src);
				$width = $info[0];
				$height = $info[1];
				$ext = image_type_to_extension($info[2], false);

				if($ext != 'gif'){//gif类型的图片不做任何处理
					$flag = false;
					if(!empty($exif['Orientation'])){
						switch($exif['Orientation']){
				        		case 6:
				            			$width = $info[1];
								$height = $info[0];
								$flag = true;
				            			break;
				        		case 8:
				            			$width = $info[1];
								$height = $info[0];
								$flag = true;
				            			break;
				    			}	
					}

					if($type=='headImage'){
						$image->thumb(200,200);
					} elseif($type=='coverImage'){
						$flag ? $image->thumb(400,640) : $image->thumb(640,400);
					} else{
						if($width > 800){
							$flag ? $image->thumb((800*$height)/$width, 800) : $image->thumb(800,(800*$height)/$width);
						} else{
							$flag ? $image->thumb($height, $width) : $image->thumb($width,$height);
						}
					}
				}	
				// $image->show();//输出到浏览器
				$newName = 'new_' . basename($src);
				 

				//将处理后的图片做相应旋转（有些图片不正）
				if($ext != 'gif'){
					$image->save($newName, $dir);//保存到硬盘
					$image->rotate($dir . '/' . $newName, $exif);
					$data['path'][] = $dir2 . '/' . $newName;
				} else{
					$data['path'][] = $src;
				}
			}	
			
			if($data){
				return Response::show(200, "图片上传成功", $data);
			} else{
				return Response::show(200, "图片上传失败", $data);
			}
		}
	}

	public function articleImage(){//文章图片
		$file = isset($_FILES['avatar']) ? $_FILES['avatar'] : '';
		$isWater = isset($_POST['iswater']) ? $_POST['iswater'] : '';
		$uid = isset($_POST['uid']) ? $_POST['uid'] : '';

		if(isset($_FILES['avatar'])){
			//将图片上传至服务器
			$day = date('Ymd');
			$upload= new upload('avatar', BASE_URL.'/Finance-Images/articleImage/'.$day.'/'.$uid);
			$num = count($_FILES['avatar']['name']);
			$dest = $upload->uploadFile($num);
			//var_dump($dest);die;

			//给原始图片先压缩，再旋转（需要旋转的），再加上水印图片
			$src = $dest[0];
            //echo $src;die;
			$dir = dirname($src);
			$dir2 = str_replace('/FinanceDisk/Finance-Images', IMG_URL, $dir);
			$image = new Image($src);
			//echo $src;
            //var_dump($image);die;

			//处理图片前保存拍照方向参数
			$exif = @exif_read_data($src);

			$info = $image->getSize($src);
			$width = $info[0];
			$height = $info[1];
			$ext = image_type_to_extension($info[2], false);
			
			if($ext != 'gif'){//gif类型的图片不做任何处理
				$flag = false;
				if(!empty($exif['Orientation'])){
					switch($exif['Orientation']){
			        		case 6:
			            			$width = $info[1];
							        $height = $info[0];
							        $flag = true;
			            			break;
			        		case 8:
			            			$width = $info[1];
							        $height = $info[0];
							        $flag = true;
			            			break;
			    		}	
				}
		
				if($width > 800){
					$flag ? $image->thumb((800*$height)/$width,800) : $image->thumb(800, (800*$height)/$width);
					$local_image['y'] = ((800*$height)/$width-35-10);
					$local_image['x'] = 790 - 121;
				} else{
					$flag ? $image->thumb($height, $width) : $image->thumb($width,$height);
					$local_image['y'] = ($height-35-10);
					$local_image['x'] = $width - 131;
				}
			}

			// $image->show();//输出到浏览器
			$newName = 'new_' . basename($src);

			if($ext != 'gif'){
				$image->save($newName, $dir);//保存到硬盘
				//将处理后的图片做相应旋转（有些图片不正）
				$image->rotate($dir . '/' . $newName, $exif);
				//添加图片水印
				if($isWater == '1'){
					$water = BASE_URL . '/Finance-openapi/tools/image/image/watermark.png';
					$filename = $dir . '/' . $newName;
					$image->imageMark($filename,$water,$local_image);
				}
				$data['Image'] = $dir2 . '/' . $newName;
				$data['picture'] = $dir2 . '/' . $newName;
			} else{
				$data['Image'] = $src;
				$data['picture'] = $src;
			}

			$data['width'] = $width;
			$data['height'] = $height;

			if($data){
				return Response::show(200, "图片上传成功", $data);
			} else{
				return Response::show(400, "图片上传失败", $data);
			}
		}
	}
}