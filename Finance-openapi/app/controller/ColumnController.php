<?php

class ColumnController{
	public function select(){//全部栏目信息
		/*$cache = new File();
		$data = array();
		//检查缓存文件是否存在：
			//1.存在，则获取缓存内容，赋值给$data
			//2.不存在，则从数据库中获取数据，并缓存
		if(!$data = $cache->cacheData('column_select_cache')){*/

			$connect = Db::getInstance()->connect();
			$sql = "SELECT * FROM [columnInfo] ORDER BY [sort] ASC";
			$result = sqlsrv_query($connect, $sql);
			$i = 0;
			while($row = sqlsrv_fetch_array($result)){
				$data[$i]['Lmid'] 	= $row['Lmid'];
				$data[$i]['Lmname'] = $row['Lmname'];
				$i++;
			}
			sqlsrv_close($connect);

		/*	if($data){
				$cache->cacheData('column_cache', $data, 1200);
			}
		}*/

		//制作接口数据，返回给客户端
		if($data){
			return Response::show(200, "获取栏目成功", $data);
		} else{
			return Response::show(400, "获取栏目失败", $data);
		}
	}
}
