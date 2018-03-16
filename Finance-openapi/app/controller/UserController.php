<?php

class UserController{
	public function getUserDetail(){//用户信息
		$uid = isset($_POST['uid']) ? $_POST['uid'] : '0';

		$connect = Db::getInstance()->connect();
		$sql = "SELECT TOP 1 [a].[Uid],[a].[Addresses],[a].[Sex],[a].[Birthday],[a].[autograph],[a].[Headportrait],[b].[NickName],[b].[RoleId],[a].[CoverImg] 
				FROM [userDetailed] [a] INNER JOIN [userInfo] [b] 
				ON [a].[Uid]=[b].[Uid]
				WHERE [a].[Uid] = '" . $uid . "'";
		$result = sqlsrv_query($connect, $sql);
		if(!$result){
			sqlsrv_close($connect);
			return Response::show(200, "用户信息为空");
		}
		$row = sqlsrv_fetch_array($result);
		sqlsrv_close($connect);
		$data = array();
		$data['Uid'] 			= $row['Uid'];
		$data['Addresses'] 		= $row['Addresses'];
		$data['Birthday'] 		= null;
		if($row['Birthday']){
			$data['Birthday'] 	=$row['Birthday']->format("Y-m-d");
		}
		$data['Sex']		 	= $row['Sex'];
		$data['autograph']		= $row['autograph'];
		$data['NickName']		= $row['NickName'];
		$data['Headportrait']		= IMG_URL . $row['Headportrait'];
		$data['RoleId'] 		= $row['RoleId'];
		$data['CoverImg'] 		= IMG_URL . $row['CoverImg'];

		if($data){
			return Response::show(200, "获取用户信息成功", $data);
		} else{
			return Response::show(200, "用户信息为空", $data);
		}
	}

	public function updateUser(){//修改用戶信息
		$updateFiled 	= isset($_POST['updateFiled']) ? $_POST['updateFiled'] : '';
		$uid 		= isset($_POST['uid']) ? $_POST['uid'] : '';//不能为空

		$fields = explode(',', $updateFiled);
		$str = $str2 = "";
		for($i=0,$len=count($fields);$i<$len;$i++){
			if($fields[$i] == 'NickName')
				continue;
			if($fields[$i] == 'BirthDay'){
				$str .= $fields[$i] . "='" . date('Y-m-d H:i:s', $_POST['BirthDay']) . "',";
				continue;
			}
			if($fields[$i] == 'Headportrait'){
				$_POST['Headportrait'] = str_replace(IMG_URL, '', $_POST['Headportrait']);
				$str .= $fields[$i] . "='" . $_POST['Headportrait'] . "',";
				continue;
			}
			if($fields[$i] == 'CoverImg'){
				$_POST['CoverImg'] = str_replace(IMG_URL, '', $_POST['CoverImg']);
				$str .= $fields[$i] . "='" . $_POST['CoverImg'] . "',";
				continue;
			}
			$str .= $fields[$i] . "='" . $_POST[$fields[$i]] . "',";
		}
		$str = rtrim($str, ',');
        //selfEcho($str);
		$connect = Db::getInstance()->connect();
		if($str != ''){
			$sql = "update [UserDetailed] set " . $str . " where Uid='" . $uid . "'";
			$result = sqlsrv_query($connect, $sql);
		}

        if(in_array('NickName', $fields)){
            $sql2 = "update [userInfo] set NickName='" . $_POST['NickName'] . "' where Uid='" . $uid . "'";
            $result2 = sqlsrv_query($connect, $sql2);
        }
        sqlsrv_close($connect);
        if($result || $result2){
            return Response::show(200, "修改用户信息成功");
        } else{
            return Response::show(400, "修改用户信息失败");
        }
	}

	public function thirdLogin(){//第三方登录	
		$openid 		= isset($_POST['openid']) ? $_POST['openid'] : '';
		$nickName 		= isset($_POST['nickName']) ? $_POST['nickName'] : '';
		$headportrait 		= isset($_POST['headportrait']) ? $_POST['headportrait'] : '';
		$sex 			= isset($_POST['sex']) ? $_POST['sex'] : '';

		$connect = Db::getInstance()->connect();
		//var_dump($connect);exit;
		$sql = "EXEC [ThirDlOGIN] @openid = '" . $openid . "', @nickName = '" . $nickName . "', @headportrait = '" . $headportrait . "', @sex = '" . $sex . "'";
		//echo $sql;exit;
		$result = sqlsrv_query($connect, $sql);
		//var_dump($connect);exit;
		if(!$result){
			sqlsrv_close($connect);
			return Response::show(400, "登陆失败");
		}
		$row = sqlsrv_fetch_array($result);
		sqlsrv_close($connect);
		$data['uid'] = $row['Uid'];
		$data['UTokenkey'] = $row['UTokenkey'];
		return Response::show(200, "登陆成功",$data);
	}
}
