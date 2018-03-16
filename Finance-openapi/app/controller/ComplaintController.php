<?php

class ComplaintController{
	public function getComplaintList(){//投诉列表
		$uid 			= isset($_POST['uid']) ? $_POST['uid'] : '';//不可能为空
		$pageSize 		= isset($_POST['pageSize']) ? $_POST['pageSize'] : 10;
		$maxCreateTime 		= isset($_POST['maxCreateTime']) ? $_POST['maxCreateTime'] : '';//最新投诉时间不为空，用于刷新，可能为空
		$minCreateTime 		= isset($_POST['minCreateTime']) ? $_POST['minCreateTime'] : '';//最旧投诉时间不为空，用于加载

		$connect = Db::getInstance()->connect();
		$sql   = "SELECT TOP " . $pageSize . " [CID],[CTitle],[CContent],[Images],[CreateDate],[CreateUID], ";
		$sql  .= "(SELECT TOP 1 [NickName] FROM [UserInfo] WHERE [Uid]='" . $uid . "') AS 'NickName', ";
		$sql  .= "(SELECT TOP 1 [Headportrait] FROM [UserDetailed] WHERE [Uid]='" . $uid . "') AS 'Headportrait' FROM [Complaint] WHERE ";
		if($maxCreateTime == '' && $minCreateTime != ''){//最旧文章时间不为空
			$sql .= " DATEDIFF(SECOND,CONVERT(datetime,'" . date("Y-m-d H:i:s", $minCreateTime) . "',101), [CreateDate]) < 0 AND ";
		} elseif($maxCreateTime != '' && $minCreateTime == ''){//最新文章时间不为空
			$sql .= " DATEDIFF(SECOND,CONVERT(datetime,'" . date("Y-m-d H:i:s", $maxCreateTime) . "',101), [CreateDate]) > 0 AND ";
		}
			$sql .= " [isDel] = 0 AND [CreateUID] = '" . $uid . "' ORDER BY [CreateDate] DESC";
		//echo $sql;exit;
		$result = sqlsrv_query($connect, $sql);

		if(!$result){
			sqlsrv_close($connect);
			return Response::show(200, "投诉为空");
		}

		$data = array();
		$i = 0;
		while($row = sqlsrv_fetch_array($result)){
			$data[$i]['CID'] 			= $row['CID'];
			$data[$i]['CTitle'] 			= $row['CTitle'];
			$data[$i]['CContent']			= $row['CContent'];
			//$data[$i]['Images']			= $row['Images'];	
			$data[$i]['CreateDate'] 		= strtotime($row['CreateDate']->format("Y-m-d H:i:s"));	
			$data[$i]['user']['NickName'] 		= $row['NickName']; 
			$data[$i]['user']['Headportrait'] 	= IMG_URL . $row['Headportrait']; 

			if($row['Images'] == ''){
				$data[$i]['imageList'] 	= [];
			} else{
				$images = explode(',', $row['Images']);	
				for($j=0,$len=count($images);$j<$len;$j++){
					$data[$i]['imageList'][$j] = IMG_URL . $images[$j]; 
				}		
			}	
			$i++;
		}
		sqlsrv_close($connect);
		//var_dump($data);exit;
		if($data){
			return Response::show(200, "获取投诉成功", $data);
		} else{
			return Response::show(200, "投诉为空", $data);
		}
	}

	public function addComplaint(){//发布投诉
		$cTitle 		= isset($_POST['cTitle']) ? $_POST['cTitle'] : '';
		$cContent 		= isset($_POST['cContent']) ? $_POST['cContent'] : '';
		$createUid 		= isset($_POST['createUid']) ? $_POST['createUid'] : '';
		$telephone 		= isset($_POST['telephone']) ? $_POST['telephone'] : '';
		$images 		= ''; 

		if(isset($_POST['images'])){
			$temp = explode(',', $_POST['images']);
			for($i=0,$len=count($temp);$i<$len;$i++){
				$temp[$i] = str_replace(IMG_URL, '', $temp[$i]);
			}
			$images = implode(',', $temp);
		}

		$connect = Db::getInstance()->connect();
		$sql = "INSERT INTO [Complaint] (CTitle,CContent,Images,CreateUID,Telephone) 		
				VALUES('" . $cTitle . "','" . $cContent . "','" . $images . "','" . $createUid . "','" . $telephone . "');";
		$result = sqlsrv_query($connect, $sql);
		sqlsrv_close($connect);

		if($result){
			return Response::show(200, "投诉成功");
		} else{
			return Response::show(400, "投诉失败");
		}
	}

	public function delComplaint(){//删除投诉
		$cID = isset($_POST['cID']) ? $_POST['cID'] : '';

		$connect = Db::getInstance()->connect();
		$sql = "UPDATE [Complaint] SET [isDel]='1' WHERE [CID]='" . $cID . "'";
		$result = sqlsrv_query($connect, $sql);
		sqlsrv_close($connect);

		if($result){
			return Response::show(200, "删除投诉成功");
		} else{
			return Response::show(400, "删除投诉失败");
		}
	}
}