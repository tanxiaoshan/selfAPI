<?php

class ADinfoController{

	public function getAdList(){//广告
		$typeId = isset($_POST['typeID']) ? $_POST['typeID'] : '0';

		$connect = Db::getInstance()->connect();
		$time = date("Y-m-d H:i:s");
		$sql = "SELECT * FROM [ADinfo] 
				WHERE [IsDel] = 0
				AND '" .$time. "' BETWEEN [StartTime] AND [EndTime]
				AND [TypeID]='" . $typeId . "'";
		$result = sqlsrv_query($connect, $sql);
		$data = array();
		$i = 0;
		while($row = sqlsrv_fetch_array($result)){
			$data[$i]['ADid'] 		= $row['ADid'];
			$data[$i]['ADname'] 		= $row['ADname'];
			if(stripos($row['ADImage'], 'http://') !== false){
				$data[$i]['ADImage'] 	= $row['ADImage'];

			}else{
				$data[$i]['ADImage'] 	= IMG_URL . $row['ADImage'];
			}
			$data[$i]['ADLink'] 		= $row['ADLink'];
			$data[$i]['Weight']		= $row['Weight'];
			$data[$i]['Height']		= $row['Height'];
			$data[$i]['StartTime'] 	  	= strtotime($row['StartTime']->format("Y-m-d H:i:s"));
			$data[$i]['EndTime']		= strtotime($row['EndTime']->format("Y-m-d H:i:s"));
			$data[$i]['TypeID'] 		= $row['TypeID'];
			$data[$i]['sort'] 		= $row['sort'];
			$i++;
		}
		sqlsrv_close($connect);

		if(!$result){
			return Response::show(200, "广告为空");
		}

		if($data){
			return Response::show(200, "获取广告成功", $data);
		} else{
			return Response::show(200, "广告为空", $data);
		}
	}
}