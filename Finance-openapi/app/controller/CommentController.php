<?php

class CommentController{
	public function addComment(){//发布评论
		$articleID 		= isset($_POST['articleID']) ? $_POST['articleID'] : '';
		$uid 			= isset($_POST['uid']) ? $_POST['uid'] : '';
		$isTheme 		= isset($_POST['isTheme']) ? $_POST['isTheme'] : '';
		$contents 		= isset($_POST['contents']) ? $_POST['contents'] : '';
		//$contents = $this->unicode_encode($contents);

		$connect = Db::getInstance()->connect();
		$sql = "INSERT INTO [commentInfo] ([Uid], [ArticleID], [Contents], [isTheme]) OUTPUT [Inserted].[commentId] VALUES ('" . $uid . "','" . $articleID . "','" . $contents . "'," . $isTheme . "); ";
		$result = sqlsrv_query($connect, $sql);

		if(!$result){
			sqlsrv_close($connect);
			return Response::show(400, "发表评论失败");
		}

		$row = sqlsrv_fetch_array($result);
		sqlsrv_close($connect);
		$data['commentId'] = $row['commentId'];

		if($data){
			return Response::show(200, "发表评论成功", $data);
		} else{
			return Response::show(400, "发表评论失败");
		}	
	}

	public function delComment(){//删除评论
		$commentID = isset($_POST['commentID']) ? $_POST['commentID'] : '';

		$connect = Db::getInstance()->connect();
		$sql = "UPDATE [commentInfo] SET [isDel]='1' WHERE [commentid]='" . $commentID . "'";
		$result = sqlsrv_query($connect, $sql);
		sqlsrv_close($connect);

		if(!$result){
			sqlsrv_close($connect);
			return Response::show(400, "删除评论失败");
		}

		if($result){
			return Response::show(200, "删除评论成功");
		} else{
			return Response::show(400, "删除评论失败");
		}
	}

	public function getComment(){//全部评论
		$articleID 		= isset($_POST['articleID']) ? $_POST['articleID'] : '';
		$uid 			= isset($_POST['uid']) ? $_POST['uid'] : '';
		$pageSize 		= isset($_POST['pageSize']) ? $_POST['pageSize'] : '10';
		$maxCreateTime 		= isset($_POST['maxCreateTime']) ? $_POST['maxCreateTime'] : '';//最新评论时间不为空，用于刷新，可能为空
		$minCreateTime 		= isset($_POST['minCreateTime']) ? $_POST['minCreateTime'] : '';//最旧评论时间不为空，用于加载

		$connect = Db::getInstance()->connect();
		$sql = "SELECT TOP " . $pageSize . " [commentid],[Contents],[CreateTime],[Uid]";
		if($uid != ''){
			$sql .= ", (SELECT COUNT(1) isZan FROM [commentInfo_Good] WHERE [commentInfo_Good].[Commentid]=[commentInfo].[commentid] and Uid='" . $uid . "') AS 'isZan'";
		} else{
			$sql .= ", '0' AS 'isZan'";
		}
			$sql .= ", (SELECT COUNT(1) zan FROM [commentInfo_Good] WHERE [commentInfo_Good].[Commentid]=[commentInfo].[commentid]) AS 'zan' FROM [commentInfo] WHERE ";
		if($maxCreateTime == '' && $minCreateTime != ''){//最旧文章时间不为空
			$sql .= " DATEDIFF(SECOND,CONVERT(datetime,'" . date("Y-m-d H:i:s", $minCreateTime) . "',101), [CreateTime]) < 0 AND ";
		} elseif($maxCreateTime != '' && $minCreateTime == ''){//最新文章时间不为空
			$sql .= " DATEDIFF(SECOND,CONVERT(datetime,'" . date("Y-m-d H:i:s", $maxCreateTime) . "',101), [CreateTime]) > 0 AND ";
		}
			$sql .= " [ArticleID]='" . $articleID . "' AND [IsDel] = 0 ORDER BY [CreateTime] DESC";
		$result = sqlsrv_query($connect, $sql);

		if(!$result){
			sqlsrv_close($connect);
			return Response::show(400, "获取评论失败");
		}

		$data = array();
		$i = 0;
		while($row = sqlsrv_fetch_array($result)){
			$data[$i]['commentid'] 		= $row['commentid'];
			$data[$i]['Contents'] 		= $row['Contents'];
			$data[$i]['Uid']		= $row['Uid'];	
			$data[$i]['CreateTime'] 	= strtotime($row['CreateTime']->format("Y-m-d H:i:s"));	
			$data[$i]['isZan'] 		= $row['isZan'];
			$data[$i]['zan'] 		= $row['zan'];

			$sql4 = "select [a].[NickName],[b].[Headportrait] from [userInfo] [a] inner join [userDetailed] [b] on [a].[Uid] = [b].[Uid] where [a].[Uid]='" . $row['Uid'] . "'";
			$result4 = sqlsrv_query($connect, $sql4);
			if(!$result4){
				sqlsrv_close($connect);
				return Response::show(200, "评论为空");
			}

			$row4 = sqlsrv_fetch_array($result4);
			$data[$i]['NickName'] 	= $row4['NickName'];

			if($row4['Headportrait'] === null){
				$data[$i]['Headportrait'] 	= '';
			} else{
				$data[$i]['Headportrait'] 	= IMG_URL .$row4['Headportrait'];
			}

			$i++;
		}
		sqlsrv_close($connect);

		if($data){
			return Response::show(200, "获取评论成功", $data);
		} else{
			return Response::show(200, "评论为空", $data);
		}
	}

	public function addCommentGood(){//给评论点赞
		// $articleID 		= isset($_POST['articleID']) ? $_POST['articleID'] : '';
		$uid 			= isset($_POST['uid']) ? $_POST['uid'] : '';
		$commentID 		= isset($_POST['commentID']) ? $_POST['commentID'] : '';

		$connect = Db::getInstance()->connect();
		$sql = "select count(*) from [commentInfo_Good] where commentID='" . $commentID . "' and Uid='" . $uid . "'";
		$result = sqlsrv_query($connect, $sql);
		$row = sqlsrv_fetch_array($result);
		$num = $row[0];

		if($num == '1'){
			sqlsrv_close($connect);
			return Response::show(400, "点赞失败");
		} else{
			$sql = "insert into commentinfo_Good(Commentid, Uid) values('" . $commentID . "', '" . $uid . "')";
			$result = sqlsrv_query($connect, $sql);
			sqlsrv_close($connect);

			if($result){
				return Response::show(200, "点赞成功");
			} else{
				return Response::show(400, "点赞失败");
			}	
		}
	}

	public function unicode_encode($contents){  
	    $contents = iconv('UTF-8', 'UCS-2', $contents);  
	    $len = strlen($contents);  
	    $str = '';  
	    for($i=0;$i<$len-1;$i=$i+2){  
	        $c = $contents[$i];  
	        $c2 = $contents[$i + 1];  
	        if(ord($c) > 0){    
	        	// 两个字节的文字  
	            $str .= '\u'.base_convert(ord($c), 10, 16).base_convert(ord($c2), 10, 16);  
	        } else{  
	            $str .= $c2;  
	        }  
	    }  
	    return $str;  
	}  
}