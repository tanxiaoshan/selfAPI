<?php

class ArticleController{
    /**
     * 文章列表
     * @return string
     */
	public function getArtList(){
        //echo 123;die;
		$lmid 			= isset($_POST['Lmid']) ? $_POST['Lmid'] : '0';
		$uid 			= isset($_POST['uid']) ? $_POST['uid'] : '';
		$pageSize 		= isset($_POST['pageSize']) ? $_POST['pageSize'] : 10;
		$maxCreateTime 	= isset($_POST['maxCreateTime']) ? $_POST['maxCreateTime'] : '';//最新文章时间不为空，用于刷新，可能为空
		$minCreateTime 	= isset($_POST['minCreateTime']) ? $_POST['minCreateTime'] : '';//最旧文章时间不为空，用于加载

		$connect = Db::getInstance()->connect();
		$sql  = "SELECT TOP " . $pageSize . " [Aid], [Title], [CoverImg], [Tag], [source], [ArticleType], [visit], [visitshow], [CreateTime]";
		if($uid != ''){
			$sql .= ", (SELECT COUNT(1) [iszan] FROM [articleInfo_Good] WHERE [articleInfo_Good].[Aid] = [ArticleInfo].[Aid] AND [articleInfo_Good].[Uid] = '"     . $uid . "') AS 'iszan'";
		} else{
			$sql .= ", '0' AS 'iszan'";
		}
		$sql .= ", (SELECT COUNT(*) FROM [articleInfo_Good] WHERE [articleInfo_Good].[Aid] = [ArticleInfo].[Aid]) AS 'zan' FROM [ArticleInfo] LEFT JOIN [ArtColRelation] AS [b] ON [Aid] = [b].[ArticleID] WHERE [b].[ColumnID] = '" . $lmid . "'";
		if($maxCreateTime == '' && $minCreateTime != ''){//最旧文章时间不为空
			$sql .= " AND DATEDIFF(SECOND,CONVERT(datetime,'" . date("Y-m-d H:i:s", $minCreateTime) . "',101), [CreateTime]) < 0";
		} elseif($maxCreateTime != '' && $minCreateTime == ''){//最新文章时间不为空
			$sql .= " AND DATEDIFF(SECOND,CONVERT(datetime,'" . date("Y-m-d H:i:s", $maxCreateTime) . "',101), [CreateTime]) > 0";
		}
		$sql .= " AND [isUP] = 1 AND [isDel] = 0 ORDER BY [CreateTime] DESC";
		//echo $sql;die;
		$result = sqlsrv_query($connect, $sql);
		if(!$result){
			sqlsrv_close($connect);
			return Response::show(200, "文章列表为空");
		}

		$data = array();
		$i = 0;
		while($row = sqlsrv_fetch_array($result)){
			$data[$i]['Aid'] 			= $row['Aid'];
			$data[$i]['Title'] 			= $row['Title'];

			$coverImgArr = explode(',', $row['CoverImg']);
			$data[$i]['CoverImg'] 		= "";
			for($j=0,$len=count($coverImgArr);$j<$len;$j++){
			    if(strpos($coverImgArr[$j], 'http://img1.financeun.com') !== false){
                    $data[$i]['CoverImg']  .= $coverImgArr[$j] . ',';
                } else{
                    if($coverImgArr[$j] != '' && strpos($coverImgArr[$j], 'http://') === false){
                        $data[$i]['CoverImg']  .= IMG_URL . $coverImgArr[$j] . ',';
                    }
                }
			}
			$data[$i]['CoverImg'] 		= rtrim($data[$i]['CoverImg'], ',');

			$data[$i]['Tag'] 			= $row['Tag'];
			$data[$i]['source']		 	= $row['source'];
			$data[$i]['ArticleType'] 	= $row['ArticleType'];
			$data[$i]['visit'] 			= $row['visit'];
			$data[$i]['visitshow'] 		= $row['visitshow'];	
			$data[$i]['CreateTime'] 	= strtotime($row['CreateTime']->format("Y-m-d H:i:s"));	
			$data[$i]['isZan'] 			= $row['iszan']; 
			$data[$i]['zan'] 			= $row['zan'];
			$i++;
		}
		sqlsrv_close($connect);

		if($data){
			return Response::show(200, "获取文章列表成功", $data);
		} else{
			return Response::show(200, "文章列表为空", $data);
		}
	}

    /**
     * 文章审核列表
     * @return string
     */
	public function artCheckList(){
		$isUp 			= isset($_POST['isUp']) ? $_POST['isUp'] : '';
		$pageSize 		= isset($_POST['pageSize']) ? $_POST['pageSize'] : 10;
		$maxCreateTime 	= isset($_POST['maxCreateTime']) ? $_POST['maxCreateTime'] : '';//最新文章时间不为空，用于刷新，可能为空（第一次打开应用）
		$minCreateTime 	= isset($_POST['minCreateTime']) ? $_POST['minCreateTime'] : '';//最旧文章时间不为空，用于加载

		$connect = Db::getInstance()->connect();
		$sql  = "SELECT TOP " . $pageSize . " [Aid], [Title], [CoverImg], [source], [CreateTime],";
		$sql .= " [columnInfo] = stuff((SELECT ',' + (convert(varchar, [a].[ColumnID]) + '|' + [b].[Lmname]) FROM [ArtColRelation] AS [a] INNER JOIN 
					  [columnInfo] AS [b] ON [a].[ColumnID] = [b].[Lmid] WHERE [ArticleID] = [ArticleInfo].[Aid] for xml path('')),1,1,'')";
		$sql .= " FROM [ArticleInfo] WHERE";
		if($maxCreateTime == '' && $minCreateTime != ''){//最旧文章时间不为空
			$sql .= " DATEDIFF(SECOND,CONVERT(datetime,'" . date("Y-m-d H:i:s", $minCreateTime) . "',101), [a].[CreateTime]) < 0 AND";
		} elseif($maxCreateTime != '' && $minCreateTime == ''){//最新文章时间不为空
			$sql .= " AND DATEDIFF(SECOND,CONVERT(datetime,'" . date("Y-m-d H:i:s", $maxCreateTime) . "',101), [a].[CreateTime]) > 0 AND";
		}
		$sql .= " [isUP] = " . $isUp . " AND [isDel] = 0 ORDER BY [CreateTime] DESC";
		$result = sqlsrv_query($connect, $sql);
		if(!$result){
			sqlsrv_close($connect);
			return Response::show(200, "文章审核列表为空");
		}

		$data = array();
		$i = 0;
		while($row = sqlsrv_fetch_array($result)){
			$data[$i]['Aid'] 			= $row['Aid'];
			$data[$i]['Title'] 			= $row['Title'];

			$coverImgArr = explode(',', $row['CoverImg']);
            $data[$i]['CoverImg']  		= $coverImgArr[0];
            if(strpos($coverImgArr[0], 'http://img1.financeun.com') !== false){
                $data[$i]['CoverImg']   = $coverImgArr[0];
            } else{
                if($coverImgArr[0] != '' && strpos($coverImgArr[0], 'http://') === false){
                    $data[$i]['CoverImg']  	= IMG_URL . $coverImgArr[0];
                }
            }


			$data[$i]['source']		 	= $row['source'];	
			$data[$i]['CreateTime'] 	= strtotime($row['CreateTime']->format("Y-m-d H:i:s"));	
			$data[$i]['source']		 	= $row['source'];

			$columnArray = explode(',', $row['columnInfo']); 
			for($j=0,$len=count($columnArray);$j<$len;$j++){
				$columnArray2[$j] = explode('|', $columnArray[$j]); 
				$data[$i]['columnInfo'][$j]['Lmid'] 	= $columnArray2[$j][0];
				$data[$i]['columnInfo'][$j]['Lmname'] 	= $columnArray2[$j][1];		
			}
			$i++;
		}
		sqlsrv_close($connect);

		if($data){
			return Response::show(200, "获取文章审核列表成功", $data);
		} else{
			return Response::show(200, "文章审核列表为空", $data);
		}
	}

    /**
     * 自己发布的文章
     * @return string
     */
	public function getMyArtList(){
		$uid 			= isset($_POST['uid']) ? $_POST['uid'] : '';//不可能为空
		$pageSize 		= isset($_POST['pageSize']) ? $_POST['pageSize'] : 10;
		$isUp 			= isset($_POST['isUp']) ? $_POST['isUp'] : '0';
		$maxCreateTime 	= isset($_POST['maxCreateTime']) ? $_POST['maxCreateTime'] : '';//最新文章时间不为空，用于刷新，可能为空
		$minCreateTime 	= isset($_POST['minCreateTime']) ? $_POST['minCreateTime'] : '';//最旧文章时间不为空，用于加载

		$connect = Db::getInstance()->connect();
		$sql = "SELECT TOP " . $pageSize . " [Aid], [Title], [CoverImg], [Tag], [source], [ArticleType], [visit], [visitshow], [CreateTime]";
		if($uid != ''){
			$sql .= ", (SELECT COUNT(1) [iszan] FROM [articleInfo_Good] WHERE [articleInfo_Good].[Aid] = [ArticleInfo].[Aid] AND [articleInfo_Good].[Uid] = '" . $uid . "') AS 'iszan'";
		} else{
			$sql .= ", '0' AS 'iszan'";
		}
		$sql .= ", (SELECT COUNT(*) FROM [articleInfo_Good] WHERE [articleInfo_Good].[Aid] = [ArticleInfo].[Aid]) AS 'zan' FROM [ArticleInfo] WHERE";
		if($maxCreateTime == '' && $minCreateTime != ''){//最旧文章时间不为空
			$sql .= " DATEDIFF(SECOND,CONVERT(datetime,'" . date("Y-m-d H:i:s", $minCreateTime) . "',101), [CreateTime]) < 0 AND";
		} elseif($maxCreateTime != '' && $minCreateTime == ''){//最新文章时间不为空
			$sql .= " DATEDIFF(SECOND,CONVERT(datetime,'" . date("Y-m-d H:i:s", $maxCreateTime) . "',101), [CreateTime]) > 0 AND";
		}
		$sql .= " [Uid]='" . $uid ."' AND [isDel] = 0 ORDER BY [CreateTime] DESC";
		$result = sqlsrv_query($connect, $sql);
		if(!$result){
			sqlsrv_close($connect);
			return Response::show(200, "文章为空");
		}

		$data = array();
		$i = 0;
		while($row = sqlsrv_fetch_array($result)){
			$data[$i]['Aid'] 			= $row['Aid'];
			$data[$i]['Title'] 			= $row['Title'];
			$coverImgArr = explode(',', $row['CoverImg']);
			$data[$i]['CoverImg'] 		= "";
			for($j=0,$len=count($coverImgArr);$j<$len;$j++){
                if(strpos($coverImgArr[$j], 'http://img1.financeun.com') !== false){
                    $data[$i]['CoverImg']   .= $coverImgArr[$j] . ',';
                } else{
                    if($coverImgArr[$j] != '' && strpos($coverImgArr[$j], 'http://') === false){
                        $data[$i]['CoverImg']  	.= IMG_URL . $coverImgArr[$j] . ',';
                    }
                }
			}
			$data[$i]['CoverImg'] 		= rtrim($data[$i]['CoverImg'], ',');

			$data[$i]['Tag'] 			= $row['Tag'];
			$data[$i]['source']		 	= $row['source'];
			$data[$i]['ArticleType'] 	= $row['ArticleType'];
			$data[$i]['visit'] 			= $row['visit'];
			$data[$i]['visitshow'] 		= $row['visitshow'];	
			$data[$i]['CreateTime'] 	= strtotime($row['CreateTime']->format("Y-m-d H:i:s"));
			$data[$i]['isZan'] 			= $row['iszan']; 
			$data[$i]['zan'] 			= $row['zan'];
			$i++;
		}
		sqlsrv_close($connect);

		if($data){
			return Response::show(200, "获取文章成功", $data);
		} else{
			return Response::show(200, "文章为空", $data);
		}
	}

    /**
     * 文章详情、推荐文章列表(根据关键词)
     * @return string
     */
    public function getArticle(){
        $aid = isset($_POST['Aid']) ? $_POST['Aid'] : '';
        $uid = isset($_POST['uid']) ? $_POST['uid'] : '';

        $connect = Db::getInstance()->connect();
        $sql = "SELECT TOP 1 [keywords],[Tag], [source], [Title], [Aid], [CoverImg], [Message], [Uid], 
				(SELECT TOP 1 [UserInfo].[NickName] from [UserInfo] where [UserInfo].[Uid]=[ArticleInfo].[Uid]) AS 'NickName', [ArticleType], (SELECT STUFF((SELECT ',' + CONVERT(VARCHAR,[ArtColRelation].[ColumnID]) + '|' + (SELECT [columnInfo].[Lmname] FROM [columnInfo] WHERE [columnInfo].[Lmid] = [ArtColRelation].[ColumnID])  FROM [ArtColRelation] WHERE [ArtColRelation].[ArticleID]=[ArticleInfo].[Aid] FOR XML PATH('')),1,1,'')) AS [ColumnID], [isCom], [CreateTime] 
				FROM [ArticleInfo] 
				WHERE [Aid]='" . $aid . "' AND [isDel] = 0; ";
        //echo($sql);die;
        //$sql2 = "UPDATE [ArticleInfo] SET [visit]=[visit]+1, [visitshow]=[visitshow]+1 WHERE [Aid]='" . $aid . "'";
        $result = sqlsrv_query($connect, $sql);

        //sqlsrv_query($connect, $sql2);
        if(!$result){
            sqlsrv_close($connect);
            return Response::show(200, "文章详情为空");
        }

        $data = array();
        $i = 0;
        $row = sqlsrv_fetch_array($result);

        if(isset($row)){
            $data['Aid'] = $row['Aid'];
            $de_json = json_decode($row['Message'],TRUE);
            $len = count($de_json);
            for($i=0;$i<$len;$i++){
                if(strpos($de_json[$i]['picture'], 'http://img1.financeun.com') !== false){
                    $de_json[$i]['picture'] = $de_json[$i]['picture'];
                } else{
                    if($de_json[$i]['picture'] != '' && strpos($de_json[$i]['picture'], 'http://') === false){
                        $de_json[$i]['picture'] = IMG_URL . $de_json[$i]['picture'];
                    }
                }
            }
            $data['Message']		= json_encode($de_json);

            $data['Uid'] 			= $row['Uid'];
            $data['keywords'] 			= $row['keywords'];
            $data['NickName']		= $row['NickName'];
            $data['ArticleType'] 	= $row['ArticleType'];
            $data['isCom'] 			= $row['isCom'];
            $data['Tag'] 			= $row['Tag'];
            $data['Title'] 			= $row['Title'];
            $data['source'] 		= $row['source'];
            if($row['CreateTime']){
                $data['CreateTime']	= strtotime($row['CreateTime']->format("Y-m-d H:i:s"));
            }

            $columnArray = explode(',', $row['ColumnID']);
            for($i=0,$len=count($columnArray);$i<$len;$i++){
                $columnArray2[$i] = explode('|', $columnArray[$i]);
                $data['columnInfo'][$i]['Lmid'] 	= $columnArray2[$i][0];
                $data['columnInfo'][$i]['Lmname'] 	= $columnArray2[$i][1];
            }

            if(empty($row['keywords'])){
                $data['recommend']['support'] = '';
                $data['recommend']['data'] = [];
            } else{
                $kwArr = explode(",", $row['keywords']);
                //selfDump($kwArr);
                $str = "";
                for($i=0,$len=count($kwArr);$i<$len;$i++){
                    $str .= " CASE WHEN [title] LIKE '%" . $kwArr[$i] . "%' THEN 1 ELSE 0 END +";
                }
                $str = rtrim($str, '+');
                //selfEcho($str);
                $sql2 = "SELECT DISTINCT TOP 5 * FROM (";
                $sql2 .= "SELECT [Aid],[Title],[source],[CoverImg],[CreateTime],[visitshow]";
                if($uid != ''){
                    $sql2 .= ", (SELECT COUNT(1) [iszan] FROM [articleInfo_Good] WHERE [articleInfo_Good].[Aid] = [ArticleInfo].[Aid] AND [articleInfo_Good].[Uid] = '"
                        . $uid . "') AS 'iszan'";
                } else{
                    $sql2 .= ", '0' AS 'iszan'";
                }
                $sql2 .= ", (SELECT COUNT(*) FROM [articleInfo_Good] WHERE [articleInfo_Good].[Aid] = [ArticleInfo].[Aid]) AS 'zan', 
                      " . $str . " AS [cnt] FROM [ArticleInfo] ";
                $sql2 .= " WHERE [Aid] != '" . $aid . "' AND [isUP] = 1 AND [isDel] = 0 ";
                $sql2 .= " ) AS [t] WHERE [cnt] > 0 ORDER BY [cnt] DESC, [CreateTime] DESC";
                //selfEcho($sql2);
                $j = 0;
                $result2 = sqlsrv_query($connect, $sql2);
                $data['recommend']['support'] = '';
                $data['recommend']['data'] = [];
                while($row2 = sqlsrv_fetch_array($result2)){
                    $data['recommend']['data'][$j]['Aid'] 				= $row2['Aid'];
                    $data['recommend']['data'][$j]['Title'] 			= $row2['Title'];
                    $data['recommend']['data'][$j]['source'] 			= $row2['source'];
                    if($row2['CoverImg'] != ''){
                        $coverImgArr = explode(',', $row2['CoverImg']);
                        $data['recommend']['data'][$j]['CoverImg'] 		= IMG_URL . $coverImgArr[0];
                    } else{
                        $data['recommend']['data'][$j]['CoverImg'] 		= '';
                    }

                    if($row2['CreateTime']){
                        $data['recommend']['data'][$j]['CreateTime']	= strtotime($row2['CreateTime']->format("Y-m-d H:i:s"));
                    }
                    $data['recommend']['data'][$j]['visitshow'] 		= $row2['visitshow'];
                    $data['recommend']['data'][$j]['isZan'] 			= $row2['iszan'];
                    $data['recommend']['data'][$j]['zan'] 				= $row2['zan'];
                    $j++;
                }
            }

            sqlsrv_close($connect);
            return Response::show(200, "获取文章详情、推荐文章列表成功", $data);
        } else{
            return Response::show(200, "文章详情为空", $data);
        }
    }

    /**
     * 文章详情、推荐文章列表(根据分词插件)
     * @return string
     */
	public function getArticle2(){
		$aid = isset($_POST['Aid']) ? $_POST['Aid'] : '';
		$uid = isset($_POST['uid']) ? $_POST['uid'] : '';
		
		$connect = Db::getInstance()->connect();
		$sql = "SELECT TOP 1 [keywords],[Tag], [source], [Title], [Aid], [CoverImg], [Message], [Uid], 
				(SELECT TOP 1 [UserInfo].[NickName] from [UserInfo] where [UserInfo].[Uid]=[ArticleInfo].[Uid]) AS 'NickName', [ArticleType], (SELECT STUFF((SELECT ',' + CONVERT(VARCHAR,[ArtColRelation].[ColumnID]) + '|' + (SELECT [columnInfo].[Lmname] FROM [columnInfo] WHERE [columnInfo].[Lmid] = [ArtColRelation].[ColumnID])  FROM [ArtColRelation] WHERE [ArtColRelation].[ArticleID]=[ArticleInfo].[Aid] FOR XML PATH('')),1,1,'')) AS [ColumnID], [isCom], [CreateTime] 
				FROM [ArticleInfo] 
				WHERE [Aid]='" . $aid . "' AND [isDel] = 0; ";
		//echo($sql);die;
        //$sql2 = "UPDATE [ArticleInfo] SET [visit]=[visit]+1, [visitshow]=[visitshow]+1 WHERE [Aid]='" . $aid . "'";
	 	$result = sqlsrv_query($connect, $sql);

        //sqlsrv_query($connect, $sql2);
	 	if(!$result){
			sqlsrv_close($connect);
			return Response::show(200, "文章详情为空");
		}

		$data = array();
		$i = 0;
		$row = sqlsrv_fetch_array($result);

		if(isset($row)){
            $data['Aid'] = $row['Aid'];
            $de_json = json_decode($row['Message'],TRUE);
            $len = count($de_json);
            for($i=0;$i<$len;$i++){
                if(strpos($de_json[$i]['picture'], 'http://img1.financeun.com') !== false){
                    $de_json[$i]['picture'] = $de_json[$i]['picture'];
                } else{
                    if($de_json[$i]['picture'] != '' && strpos($de_json[$i]['picture'], 'http://') === false){
                        $de_json[$i]['picture'] = IMG_URL . $de_json[$i]['picture'];
                    }
                }

            }
            $data['Message']		= json_encode($de_json);

            $data['Uid'] 			= $row['Uid'];
            $data['keywords'] 			= $row['keywords'];
            $data['NickName']		= $row['NickName'];
            $data['ArticleType'] 	= $row['ArticleType'];
            $data['isCom'] 			= $row['isCom'];
            $data['Tag'] 			= $row['Tag'];
            $data['Title'] 			= $row['Title'];
            $data['source'] 		= $row['source'];
            if($row['CreateTime']){
                $data['CreateTime']	= strtotime($row['CreateTime']->format("Y-m-d H:i:s"));
            }

            $columnArray = explode(',', $row['ColumnID']);
            for($i=0,$len=count($columnArray);$i<$len;$i++){
                $columnArray2[$i] = explode('|', $columnArray[$i]);
                $data['columnInfo'][$i]['Lmid'] 	= $columnArray2[$i][0];
                $data['columnInfo'][$i]['Lmname'] 	= $columnArray2[$i][1];
            }

            //实例化分词插件核心类
            $so = scws_new();
            //设置分词时所用编码
            $so->set_charset('utf-8');
            //设置分词所用词典(此处使用utf8的词典)
            $so->set_dict($_SERVER['DOCUMENT_ROOT'] . '/FinanceDisk/Finance-openapi/scwsPath/dict.utf8.xdb');
            //设置分词所用规则
            $so->set_rule($_SERVER['DOCUMENT_ROOT'] . '/FinanceDisk/Finance-openapi/scwsPath/rules.utf8.ini');
            //分词前去掉标点符号
            $so->set_ignore(true);
            //是否复式分割，如“中国人”返回“中国＋人＋中国人”三个词。
            $so->set_multi(true);
            //设定将文字自动以二字分词法聚合
            $so->set_duality(true);
            //要进行分词的语句
            $so->send_text($row['Title']);
            //获取分词结果，如果提取高频词用get_tops方法
            // while ($tmp = $so->get_result()){
            //   print_r($tmp);
            // }
            $tops = $so->get_tops(1, 'n,v');
            //echo $tops[0]['word'];exit;
            $so->close();

            $sql2 = "SELECT DISTINCT TOP 5 [Aid],[Title],[source],[CoverImg],[CreateTime],[visitshow]";
            if($uid != ''){
                $sql2 .= ", (SELECT COUNT(1) [iszan] FROM [articleInfo_Good] WHERE [articleInfo_Good].[Aid] = [ArticleInfo].[Aid] AND [articleInfo_Good].[Uid] = '"
                          . $uid . "') AS 'iszan'";
            } else{
                $sql2 .= ", '0' AS 'iszan'";
            }
            $sql2 .= ", (SELECT COUNT(*) FROM [articleInfo_Good] WHERE [articleInfo_Good].[Aid] = [ArticleInfo].[Aid]) AS 'zan' FROM [ArticleInfo] LEFT JOIN
                      [ArtColRelation] AS [b] ON [Aid] = [b].[ArticleID]";
            //$sql2 .= " WHERE Title LIKE '%" . $tops[0]['word'] . "%' ";
            $sql2 .= " AND [Aid] != '" . $aid . "' AND [isUP] = 1 AND [isDel] = 0 ORDER BY [CreateTime] DESC";

            $j = 0;
            $result2 = sqlsrv_query($connect, $sql2);
            if(!$result2){
                sqlsrv_close($connect);
                return Response::show(200, "推荐文章列表为空");
            }

            $data['recommend']['support'] = '';
            $data['recommend']['data'] = '';
            while($row2 = sqlsrv_fetch_array($result2)){
                $data['recommend']['data'][$j]['Aid'] 				= $row2['Aid'];
                $data['recommend']['data'][$j]['Title'] 			= $row2['Title'];
                $data['recommend']['data'][$j]['source'] 			= $row2['source'];
                if($row2['CoverImg'] != ''){
                    $coverImgArr = explode(',', $row2['CoverImg']);
                    $data['recommend']['data'][$j]['CoverImg'] 		= IMG_URL . $coverImgArr[0];
                } else{
                    $data['recommend']['data'][$j]['CoverImg'] 		= '';
                }

                if($row2['CreateTime']){
                    $data['recommend']['data'][$j]['CreateTime']	= strtotime($row2['CreateTime']->format("Y-m-d H:i:s"));
                }
                $data['recommend']['data'][$j]['visitshow'] 		= $row2['visitshow'];
                $data['recommend']['data'][$j]['isZan'] 			= $row2['iszan'];
                $data['recommend']['data'][$j]['zan'] 				= $row2['zan'];
                $j++;
            }
            sqlsrv_close($connect);
            return Response::show(200, "获取文章详情、推荐文章列表成功", $data);
        } else{
            return Response::show(200, "文章详情为空", $data);
        }
	}

    /**
     * 文章详情v2
     * @return string
     */
	public function getArticleDetail(){
		$aid = isset($_POST['Aid']) ? $_POST['Aid'] : '';

		$connect = Db::getInstance()->connect();
		$sql = "SELECT TOP 1 [Tag], [source], [Title], [Aid], [CoverImg], [Message], [Uid], (SELECT TOP 1 [UserInfo].[NickName] from [UserInfo] where [UserInfo].[Uid]=[ArticleInfo].[Uid]) AS 'NickName', [ArticleType], (SELECT STUFF((SELECT ',' + CONVERT(VARCHAR,[ArtColRelation].[ColumnID]) + '|' + (SELECT [columnInfo].[Lmname] FROM [columnInfo] WHERE [columnInfo].[Lmid] = [ArtColRelation].[ColumnID])  FROM [ArtColRelation] WHERE [ArtColRelation].[ArticleID]=[ArticleInfo].[Aid] FOR XML PATH('')),1,1,'')) AS [ColumnID], [isCom], [CreateTime] FROM [ArticleInfo] WHERE [Aid]='" . $aid . "' AND [isDel] = 0; update ArticleInfo set visit=visit+1, visitshow=visitshow+1 where Aid='" . $aid . "'";
	 	$result = sqlsrv_query($connect, $sql);
	 	if(!$result){
			sqlsrv_close($connect);
			return Response::show(200, "文章详情为空");
		}

		$data = array();
		$i = 0;
		$row = sqlsrv_fetch_array($result);
		sqlsrv_close($connect);

		$data['Aid'] 			= $row['Aid'];

		$de_json = json_decode(iconv('gbk','utf-8',$row['Message']),TRUE);
		$len = count($de_json);
    	for($i=0;$i<$len;$i++){
			if($de_json[$i]['picture'] != '' && strpos($de_json[$i]['picture'], 'http://') === false){
				$de_json[$i]['picture'] = IMG_URL . $de_json[$i]['picture'];
			}	
    	}
        $data['Message']	    = $de_json;

		$data['Uid'] 			= $row['Uid'];
		$data['NickName']		= $row['NickName'];
		$data['ArticleType'] 	= $row['ArticleType'];
		$data['isCom'] 			= $row['isCom'];
		$data['Tag'] 			= $row['Tag'];
		$data['Title'] 			= $row['Title'];
		$data['source'] 		= $row['source'];
		$data['CreateTime']	 	= strtotime($row['CreateTime']->format("Y-m-d H:i:s"));

        $columnArray = explode(',', $row['ColumnID']);
		for($i=0,$len=count($columnArray);$i<$len;$i++){
			$columnArray2[$i] = explode('|', $columnArray[$i]); 
			$data['columnInfo'][$i]['Lmid'] 	= $columnArray2[$i][0];
			$data['columnInfo'][$i]['Lmname'] 	= $columnArray2[$i][1];		
		}	

		if($data){
			return Response::show(200, "获取文章详情成功", $data);
		} else{
			return Response::show(200, "文章详情为空", $data);
		}
	}

    /**
     * 发布和修改文章
     * @return string
     */
	public function insertOrUpdateArt(){
		$aid 				= isset($_POST['aid']) ? $_POST['aid'] : '';
		$keywords 			= isset($_POST['keywords']) ? $_POST['keywords'] : '';

		if($aid == ''){//aid为空，添加文章
			$title 			= isset($_POST['Title']) ? $_POST['Title'] : '';
			$source 		= isset($_POST['source']) ? $_POST['source'] : '';
			$uid 			= isset($_POST['Uid']) ? $_POST['Uid'] : null;
			$isCom 			= isset($_POST['isCom']) ? $_POST['isCom'] : '0';
			$lmid 			= isset($_POST['Lmids']) ? $_POST['Lmids'] : '0';	
			$articleType 	= isset($_POST['ArticleType']) ? $_POST['ArticleType'] : '1';
			$tag 			= isset($_POST['Tag']) ? $_POST['Tag'] : '1';
			$message 		= isset($_POST['Message']) ? $_POST['Message'] : '';

			$de_json = json_decode($message,TRUE);
    		$len = count($de_json);
       		for($i=0;$i<$len;$i++){
        		$de_json[$i]['picture'] = str_replace('http://img.financeun.com/', '/', $de_json[$i]['picture']);
        	}
        	$message = json_encode($de_json);

			$coverImg 		= isset($_POST['CoverImg']) ? $_POST['CoverImg'] : '';
			$createTime 	= date("Y-m-d H:i:s"); 
			$coverImgRel	= str_replace('http://img.financeun.com/', '/', $coverImg);

			$connect = Db::getInstance()->connect();
			$sql  = "DECLARE @tempTable TABLE(tempAid uniqueidentifier); ";
			$sql .= "DECLARE @tempAid uniqueidentifier; ";
			$sql .= "INSERT INTO [articleInfo] ([Title],[CoverImg], [Message], [Tag], [source], [Uid], [CreateTime], [ArticleType], [Lmid], [visit], [isCom],[isDel],[keywords]) OUTPUT [Inserted].[Aid] INTO @tempTable VALUES (N'" . $title . "','" . $coverImgRel . "',N'" . $message . "','" . $tag . "',N'" . $source . "','" . $uid . "', '" . $createTime  . "', '" . $articleType . "', '0', '0', '" . $isCom . "', '0', N'" . $keywords . "'); ";
			$sql .= "SELECT @tempAid = tempAid FROM @tempTable; ";

			$lmids = explode(",", $lmid);//将栏目ID字符串分割为数组
			for($i=0,$len=count($lmids);$i<$len;$i++){//组装插入的批量数据
				$sql .= "INSERT INTO [ArtColRelation] ([ArticleID], [ColumnID]) VALUES (@tempAid, '" . $lmids[$i] . "'); ";			
			}
			$sql .= "SELECT 'OK'";

			$result = sqlsrv_query($connect, $sql);
			sqlsrv_close($connect);

			if($result){
				return Response::show(200, "添加文章成功");
			} else{
				return Response::show(400, "添加文章失败");
			}
		} else{//修改文章
			// $updateField 	= isset($_POST['UpdateField']) ? $_POST['UpdateField'] : '';
			$title 			= isset($_POST['Title']) ? $_POST['Title'] : '';
			$source 		= isset($_POST['source']) ? $_POST['source'] : '';
			$uid 			= isset($_POST['Uid']) ? $_POST['Uid'] : '';
			$isCom 			= isset($_POST['isCom']) ? $_POST['isCom'] : '1';
			$lmid 			= isset($_POST['Lmids']) ? $_POST['Lmids'] : '1';
			$articleType 	= isset($_POST['ArticleType']) ? $_POST['ArticleType'] : '1';
			$tag 			= isset($_POST['tag']) ? $_POST['tag'] : '1';
			$message 		= isset($_POST['Message']) ? $_POST['Message'] : '';

			$de_json = json_decode($message,TRUE);
    		$len = count($de_json);
       		for($i=0;$i<$len;$i++){
        		$de_json[$i]['picture'] = str_replace(IMG_URL, '/', $de_json[$i]['picture']);
        	}
        	$message = json_encode($de_json);

			$coverImg 		= isset($_POST['CoverImg']) ? $_POST['CoverImg'] : '';
			$coverImgRel	= str_replace(IMG_URL, '/', $coverImg);

			//插入主要数据至ArticleInfo表中（Lmid字段除外）
			$connect = Db::getInstance()->connect();
			$sql  = "UPDATE [articleInfo] SET [Title]= N'" . $title . "',  [source] = N'" . $source . "',  [Uid] = '" . $uid . "', [isCom] = '" . $isCom . "', [ArticleType] = '" . $articleType . "', [Tag] = '" . $tag . "', [Message] = N'" . $message . "', [CoverImg] = '" . $coverImgRel. "', [keywords] = N'" . $keywords . "'  WHERE [Aid] = '" . $aid . "'; ";
			$sql .= "DELETE FROM [ArtColRelation] WHERE [ArticleID] = '" . $aid . "'; ";

            $lmids = explode(",", $lmid);
            for($i=0,$len=count($lmids);$i<$len;$i++){
                $sql .= "INSERT INTO [ArtColRelation] ([ArticleID], [ColumnID]) VALUES ('" . $aid . "', '" . $lmids[$i] . "'); ";
            }
            $sql .= "SELECT 'OK'";

			$result = sqlsrv_query($connect, $sql);
			sqlsrv_close($connect);
			
			if($result){
				return Response::show(200, "修改文章成功");
			} else{
				return Response::show(400, "修改文章失败");
			}
		}	
	}

    /**
     * 发布和修改文章(unicode)
     * @return string
     */
	public function insertOrUpdateArtUnicode(){
		$aid 				= isset($_POST['aid']) ? $_POST['aid'] : '';

		if($aid == ''){//aid为空，添加文章
			$title 			= isset($_POST['Title']) ? $_POST['Title'] : '';
			$source 		= isset($_POST['source']) ? $_POST['source'] : '';
			$uid 			= isset($_POST['Uid']) ? $_POST['Uid'] : null;
			$isCom 			= isset($_POST['isCom']) ? $_POST['isCom'] : '0';
			$lmid 			= isset($_POST['Lmids']) ? $_POST['Lmids'] : '0';	
			$articleType 	= isset($_POST['ArticleType']) ? $_POST['ArticleType'] : '1';
			$tag 			= isset($_POST['tag']) ? $_POST['tag'] : '1';
			$message 		= isset($_POST['Message']) ? $_POST['Message'] : '';
			$coverImg 		= isset($_POST['CoverImg']) ? $_POST['CoverImg'] : '';
			$createTime 	= date("Y-m-d H:i:s"); 
			$coverImgRel	= str_replace('http://img.financeun.com', '/', $coverImg);
			$title 			= utf8_to_unicode_str($title);
			$source 		= utf8_to_unicode_str($source);

			$de_json = json_decode($message,TRUE);
       		for($i=0,$len = count($de_json);$i<$len;$i++){
        		if($de_json[$i]['picture'] != ''){
        			$de_json[$i]['picture'] = str_replace(IMG_URL, '/', $de_json[$i]['picture']);
        		}
        		$de_json[$i]['content'] = utf8_to_unicode_str($de_json[$i]['content']);
        	}
        	$message = json_encode($de_json);

			$connect = Db::getInstance()->connect();
			$sql  = "DECLARE @tempTable TABLE(tempAid uniqueidentifier); ";
			$sql .= "DECLARE @tempAid uniqueidentifier; ";
			$sql .= "INSERT INTO [articleInfo] ([Title],[CoverImg], [Message], [Tag], [source], [Uid], [CreateTime], [ArticleType], [Lmid], [visit], [isCom],[isDel]) OUTPUT [Inserted].[Aid] INTO @tempTable VALUES ('" . $title . "','" . $coverImgRel . "','" . $message . "','" . $tag . "','" . $source . "','" . $uid . "', '" . $createTime  . "', '" . $articleType . "', '0', '0', '" . $isCom . "', '0'); ";
			$sql .= "SELECT @tempAid = tempAid FROM @tempTable; ";	

			$lmids = explode(",", $lmid);//将栏目ID字符串分割为数组
			for($i=0,$len=count($lmids);$i<$len;$i++){//组装插入的批量数据
				$sql .= "INSERT INTO [ArtColRelation] ([ArticleID], [ColumnID]) VALUES (@tempAid, '" . $lmids[$i] . "'); ";			
			}
			$sql .= "SELECT 'OK'";

			$result = sqlsrv_query($connect, $sql);
			sqlsrv_close($connect);

			if($result){
				return Response::show(200, "添加文章成功");
			} else{
				return Response::show(400, "添加文章失败");
			}
		} else{//修改文章
			// $updateField 	= isset($_POST['UpdateField']) ? $_POST['UpdateField'] : '';
			$title 			= isset($_POST['Title']) ? $_POST['Title'] : '';
			$source 		= isset($_POST['source']) ? $_POST['source'] : '';
			$uid 			= isset($_POST['Uid']) ? $_POST['Uid'] : '';
			$isCom 			= isset($_POST['isCom']) ? $_POST['isCom'] : '1';
			$lmid 			= isset($_POST['Lmids']) ? $_POST['Lmids'] : '1';
			$articleType 	= isset($_POST['ArticleType']) ? $_POST['ArticleType'] : '1';
			$tag 			= isset($_POST['tag']) ? $_POST['tag'] : '1';
			$message 		= isset($_POST['Message']) ? $_POST['Message'] : '';
			$title 			= utf8_to_unicode_str($title);
			$source 		= utf8_to_unicode_str($source);

			$de_json = json_decode($message,TRUE);
    		$len = count($de_json);
        	for($i=0;$i<$len;$i++){
        		if($de_json[$i]['picture'] != ''){
				$de_json[$i]['picture'] = str_replace(IMG_URL, '/', $de_json[$i]['picture']);
        		}
        		$de_json[$i]['content'] =utf8_to_unicode_str($de_json[$i]['content']);
        	}
       	 	$message = json_encode($de_json);

			$coverImg 		= isset($_POST['CoverImg']) ? $_POST['CoverImg'] : '';
			$coverImgRel	= str_replace(IMG_URL, '/', $coverImg);

			//插入主要数据至ArticleInfo表中（Lmid字段除外）
			$connect = Db::getInstance()->connect();
			$sql  = "UPDATE [articleInfo] SET [Title]= '" . $title . "',  [source] = '" . $source . "',  [Uid] = '" . $uid . "', [isCom] = '" . $isCom . "', [ArticleType] = '" . $articleType . "', [Tag] = '" . $tag . "', [Message] = '" . $message . "', [CoverImg] = '" . $coverImgRel . "'  WHERE [Aid] = '" . $aid . "'; ";
			$sql .= "DELETE FROM [ArtColRelation] WHERE [ArticleID] = '" . $aid . "'; ";

			$lmids = explode(",", $lmid);
			for($i=0,$len=count($lmids);$i<$len;$i++){
				$sql .= "INSERT INTO [ArtColRelation] ([ArticleID], [ColumnID]) VALUES ('" . $aid . "', '" . $lmids[$i] . "'); ";			
			}
			$sql .= "SELECT 'OK'";

			$result = sqlsrv_query($connect, $sql);
			sqlsrv_close($connect);

			if($result){
				return Response::show(200, "修改文章成功");
			} else{
				return Response::show(400, "修改文章失败");
			}
		}	
	}

    /**
     * 后台审核文章
     * @return string
     */
	public function adminUpdateArt(){
		$aid 			= isset($_POST['aid']) ? $_POST['aid'] : '';
		$UpdateField 	= isset($_POST['UpdateField']) ? $_POST['UpdateField'] : '';

		$fields = explode(',', $UpdateField);
		$str = "";
		for($i=0,$len=count($fields);$i<$len;$i++){
			if($fields[$i] === 'Lmids'){
				$lmids = isset($_POST[$fields[$i]]) ? $_POST[$fields[$i]] : '';
				$str .= "Lmid='0',";
			} elseif($fields[$i] === 'Message'){
				$de_json = json_decode($_POST['Message'], TRUE);
	       		for($j=0,$len = count($de_json);$j<$len;$j++){
	        		$de_json[$j]['picture'] = str_replace('http://img.financeun.com/', '/', $de_json[$j]['picture']);
					$de_json[$j]['content'] = str_replace('•', '·', $de_json[$j]['content']); 
	        	}
	        	$message = json_encode($de_json);
				$str .= $fields[$i] . "='" . $message . "',";
			} elseif($fields[$i] === 'CoverImg'){
				$coverImgRel = str_replace('http://img.financeun.com/', '/', $_POST['CoverImg']);
				$str .= $fields[$i] . "='" . $coverImgRel . "',";
			} else{
				$tmpField = isset($_POST[$fields[$i]]) ? $_POST[$fields[$i]] : '';
				$str .= $fields[$i] . "='" . $tmpField . "',";
			}
		}
		$str = rtrim($str, ',');

		//插入主要数据至ArticleInfo表中（Lmid字段除外）
		$connect = Db::getInstance()->connect();
		$sql = "UPDATE [articleInfo] SET " . $str . " WHERE [Aid] = '" . $aid . "'; ";
		//echo $sql;die;
		$result = sqlsrv_query($connect, $sql);
		
		if($lmids != ''){
			$sql2 = "DELETE FROM [ArtColRelation] WHERE [ArticleID] = '" . $aid . "'; ";
			$result2 = sqlsrv_query($connect, $sql2);

			$lmids = explode(",", $lmids);
			for($i=0,$len=count($lmids);$i<$len;$i++){
				$sql3 = "INSERT INTO [ArtColRelation] ([ArticleID], [ColumnID]) VALUES ('" . $aid . "', '" . $lmids[$i] . "'); ";
				$result3 = sqlsrv_query($connect, $sql3);		
			}
		}
		sqlsrv_close($connect);

		if($result || ($result2 && $result3)){
			return Response::show(200, "修改文章成功");
		} else{
			return Response::show(400, "修改文章失败");
		}
	}

    /**
     * 后台审核文章V2（包括message）
     * @return string
     */
	public function adminUpdateArt2(){
		$aid 			= isset($_POST['aid']) ? $_POST['aid'] : '';
		$UpdateField 	= isset($_POST['UpdateField']) ? $_POST['UpdateField'] : '';

		$fields = explode(',', $UpdateField);
		$str = "";
		for($i=0,$len=count($fields);$i<$len;$i++){
			if($fields[$i] === 'Lmids'){
				$lmids = isset($_POST[$fields[$i]]) ? $_POST[$fields[$i]] : '';
				$str .= "Lmid='0',";
			} elseif($fields[$i] === 'Message'){
				$de_json = json_decode($_POST['Message'], TRUE);
	        	foreach($de_json as $k=>$v){
	        		if($v['picture'] != '')
						$v['picture'] = str_replace('http://img1.financeun.com/', '/', $v['picture']);
					if($v['content'] != '')
						$v['content'] = str_replace('•', '·', $v['content']); 
	        	}
	        	$message = json_encode($de_json);
				$str .= $fields[$i] . "='" . $_POST['Message'] . "',";
			} elseif($fields[$i] === 'CoverImg'){
				$coverImgRel = str_replace('http://img.financeun.com/', '/', $_POST['CoverImg']);
				$str .= $fields[$i] . "='" . $coverImgRel . "',";
			} else{
				$tmpField = isset($_POST[$fields[$i]]) ? $_POST[$fields[$i]] : '';
				$str .= $fields[$i] . "='" . $tmpField . "',";
			}
		}
		$str = rtrim($str, ',');

		//插入主要数据至ArticleInfo表中（Lmid字段除外）
		$connect = Db::getInstance()->connect();
		$sql = "UPDATE [articleInfo] SET " . $str . " WHERE [Aid] = '" . $aid . "'; ";
		
		if($lmids != ''){
			$sql .= "DELETE FROM [ArtColRelation] WHERE [ArticleID] = '" . $aid . "'; ";

			$lmids = explode(",", $lmids);
			for($i=0,$len=count($lmids);$i<$len;$i++){
				$sql .= "INSERT INTO [ArtColRelation] ([ArticleID], [ColumnID]) VALUES ('" . $aid . "', '" . $lmids[$i] . "'); ";			
			}
		}
		$result = sqlsrv_query($connect, $sql);
		sqlsrv_close($connect);

		if($result){
			return Response::show(200, "修改文章成功");
		} else{
			return Response::show(400, "修改文章失败");
		}
	}

    /**
     * 删除文章
     * @return string
     */
	public function delArtList(){
		$aid = isset($_POST['aid']) ? $_POST['aid'] : '';

		$connect = Db::getInstance()->connect();
		$sql = "update [ArticleInfo] set [isDel]='1' where [Aid]='" . $aid . "'";
		$result = sqlsrv_query($connect, $sql);
		sqlsrv_close($connect);

		if($result){
			return Response::show(200, "删除文章成功");
		} else{
			return Response::show(400, "删除文章失败");
		}
	}

    /**
     * 搜索文章
     * @return string
     */
	public function search(){
		$query 			= isset($_POST['query']) ? $_POST['query'] : '';
		$uid 			= isset($_POST['uid']) ? $_POST['uid'] : '';
		$pageSize 		= isset($_POST['pagesize']) ? $_POST['pagesize'] : 10;
		$maxCreateTime 	= isset($_POST['maxCreateTime']) ? $_POST['maxCreateTime'] : '';//最新文章时间不为空，用于刷新，可能为空
		$minCreateTime 	= isset($_POST['minCreateTime']) ? $_POST['minCreateTime'] : '';//最旧文章时间不为空，用于加载

		$connect = Db::getInstance()->connect();
		$sql = "SELECT TOP " . $pageSize . " [Aid], [Title], [CoverImg], [Tag], [source], [ArticleType], [visit], [visitshow], [CreateTime]";
		if($uid != ''){
			$sql .= ", (SELECT COUNT(1) [iszan] FROM [articleInfo_Good] WHERE [articleInfo_Good].[Aid] = [ArticleInfo].[Aid] AND [articleInfo_Good].[Uid] = '" . $uid . "') AS 'iszan'";
		} else{
			$sql .= ", '0' AS 'iszan'";
		}
		$sql .= ", (SELECT COUNT(*) FROM [articleInfo_Good] WHERE [articleInfo_Good].[Aid] = [ArticleInfo].[Aid]) AS 'zan' FROM [ArticleInfo] WHERE [Title] LIKE '%" . $query ."%'";
		if($maxCreateTime == '' && $minCreateTime != ''){//最旧文章时间不为空
			$sql .= " AND DATEDIFF(SECOND,CONVERT(datetime,'" . date("Y-m-d H:i:s", $minCreateTime) . "',101), [CreateTime]) < 0";
		} elseif($maxCreateTime != '' && $minCreateTime == ''){//最新文章时间不为空
			$sql .= " AND DATEDIFF(SECOND,CONVERT(datetime,'" . date("Y-m-d H:i:s", $maxCreateTime) . "',101), [CreateTime]) > 0";
		}
		$sql .= " AND [isUP] = 1 AND [isDel] = 0 ORDER BY [CreateTime] DESC";
		$result = sqlsrv_query($connect, $sql);
		if(!$result){
			sqlsrv_close($connect);
			return Response::show(200, "搜索文章为空");
		}

		$data = array();
		$i = 0;
		while($row = sqlsrv_fetch_array($result)){
			$data[$i]['Aid'] 			= $row['Aid'];
			$data[$i]['Title'] 			= $row['Title'];

			$coverImgArr = explode(',', $row['CoverImg']);
			$data[$i]['CoverImg'] 		= "";
			for($j=0,$len=count($coverImgArr);$j<$len;$j++){
                if(strpos($coverImgArr[$j], 'http://img1.financeun.com') !== false){
                    $data[$i]['CoverImg']  .= $coverImgArr[$j] . ',';
                } else{
                    if($coverImgArr[$j] != '' && strpos($coverImgArr[$j], 'http://') === false){
                        $data[$i]['CoverImg']  .= IMG_URL . $coverImgArr[$j] . ',';
                    }
                }

			}
			$data[$i]['CoverImg'] 		= rtrim($data[$i]['CoverImg'], ',');

			$data[$i]['Tag'] 			= $row['Tag'];
			$data[$i]['source']		 	= $row['source'];
			$data[$i]['ArticleType'] 	= $row['ArticleType'];
			$data[$i]['visit'] 			= $row['visit'];
			$data[$i]['visitshow'] 		= $row['visitshow'];	
			$data[$i]['CreateTime'] 	= strtotime($row['CreateTime']->format("Y-m-d H:i:s"));	
			$data[$i]['isZan'] 			= $row['iszan']; 
			$data[$i]['zan'] 			= $row['zan'];
			$i++;
		}
		sqlsrv_close($connect);

		if($data){
			return Response::show(200, "搜索文章成功", $data);
		} else{
			return Response::show(200, "搜索文章为空", $data);
		}
	}

    /**
     * 热门搜索
     * @return string
     */
	public function getHotSearchArtList(){
		$connect = Db::getInstance()->connect();
		$sql = "SELECT TOP 10 [Aid],[Title] FROM [ArticleInfo] WHERE DATEDIFF(DAY, getdate(), [CreateTime]) > -7 AND [isDel] = '0' ORDER BY [visit] DESC";
		$result = sqlsrv_query($connect, $sql);
		if(!$result){
			sqlsrv_close($connect);
			return Response::show(200, "热门搜索为空");
		}
		
		$data = array();
		$i = 0;
		while($row = sqlsrv_fetch_array($result)){
			$data[$i]['Aid'] 		= $row['Aid'];
			$data[$i]['Title'] 		= $row['Title'];
			$i++;
		}
		sqlsrv_close($connect);
		
		if($data){
			return Response::show(200, "获取热门搜索成功", $data);
		} else{
			return Response::show(200, "热门搜索为空", $data);
		}
	}

    /**
     * 文章点赞
     * @return string
     */
	public function zan(){
		$aid = isset($_POST['aid']) ? $_POST['aid'] : '';
		$uid = isset($_POST['uid']) ? $_POST['uid'] : '';

		$connect = Db::getInstance()->connect();
		$sql = "SELECT COUNT(1) AS [count] FROM [articleInfo_Good] WHERE [Aid] = '" . $aid . "' AND [Uid] = '" . $uid . "'; ";
        $result = sqlsrv_query($connect, $sql);
        if(!$result){
            return Response::show(400, "点赞失败");
        }
        $row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
        //selfDump($row);
        if($row['count'] == 1){
            return Response::show(400, "点赞失败");
        } else{
            $sql2 = "INSERT INTO [articleInfo_Good] ([Aid], [Uid]) VALUES ('" . $aid . "', '" . $uid . "'); ";
            $result2 = sqlsrv_query($connect, $sql2);
            if(!$result2){
                return Response::show(400, "点赞失败");
            }
            return Response::show(200, "点赞成功");
        }
		sqlsrv_close($connect);
	}

    /**
     * 机构、专题列表
     * @return string
     */
	public function theme(){
		$data = array();
        $connect = Db::getInstance()->connect();
        $sql2 = "SELECT [orgID],[orgName] FROM [organization] ORDER BY [sort]";
        //selfEcho($sql2);
        $result2 = sqlsrv_query($connect, $sql2);
        if(!$result2){
            sqlsrv_close($connect);
            return Response::show(200, "机构、专题列表为空");
        }

        $i = 0;
        while($row2=sqlsrv_fetch_array($result2)) {
            $data[$i]['orgName'] = $row2['orgName'];
            $sql3 = "SELECT [themeID],[title],[fullTitle],[logo] FROM [theme] WHERE [orgID]='" . $row2['orgID'] . "' ORDER BY [sort];";
            //selfEcho($sql3);
            $result3 = sqlsrv_query($connect, $sql3);
            if (!$result3) {
                sqlsrv_close($connect);
                return Response::show(200, "机构、专题列表为空");
            }
            $z = 0;
            while ($row3 = sqlsrv_fetch_array($result3)) {
                $data[$i]['themeList'][$z]['themeID'] = $row3['themeID'];
                $data[$i]['themeList'][$z]['title'] = $row3['title'];
                $data[$i]['themeList'][$z]['fullTitle'] = $row3['fullTitle'];
                if ($row3['logo'] != '' && strpos($row3['logo'], 'http://') === false)
                    $data[$i]['themeList'][$z]['logo'] = IMG_URL . $row3['logo'];
                else
                    $data[$i]['themeList'][$z]['logo'] = '';
                $z++;
            }
            $i++;
        }
        sqlsrv_close($connect);

		if($data){		
			return Response::show(200, "获取机构、专题列表成功", $data);
		} else{
			return Response::show(200, "机构、专题列表为空");
		}
	}

    /**
     * 品牌列表
     * @return string
     */
	public function brandList(){
		//$cache = new File();
		$data = array();
		//判断缓存是否存在
		//if(!$data = @$cache->cacheData('article_brandShow_cache')){
			$connect = Db::getInstance()->connect();
			$sql = "SELECT TOP 6 [themeID],[logo],[title],[fullTitle] FROM [theme] WHERE [brandSort] != '' ORDER BY [brandSort]";
			$result = sqlsrv_query($connect, $sql);
			if(!$result){
				sqlsrv_close($connect);
				return Response::show(200, "品牌列表为空");
			}

			$i = 0;
			while($row=sqlsrv_fetch_array($result)){
				$data[$i]['themeID']	= $row['themeID'];
				$data[$i]['logo'] 		= $row['logo'];
				if($row['logo'] != '' && strpos($row['logo'], 'http://') === false){
                    $data[$i]['logo'] 	= IMG_URL . $row['logo'];
                }

				$data[$i]['title']		= $row['title'];
				$data[$i]['fullTitle']	= $row['fullTitle'];
				$i++;			
			}
			sqlsrv_close($connect);

			//$cache->cacheData('article_brandList_cache', $data, 3600*24);
		//} 

		if($data){		
			return Response::show(200, "获取品牌列表成功", $data);
		} else{
			return Response::show(200, "品牌列表为空");
		}
	}

    /**
     * 专题详情(专题文章列表、推荐文章列表)
     * @return string
     */
	public function themeDetail(){
		$uid 			= isset($_POST['uid']) ? $_POST['uid'] : '';
		$themeID 		= isset($_POST['themeID']) ? $_POST['themeID'] : '';
		$pageSize 		= isset($_POST['pageSize']) ? $_POST['pageSize'] : 5;
		$maxCreateTime 	= isset($_POST['maxCreateTime']) ? $_POST['maxCreateTime'] : '';
		$minCreateTime 	= isset($_POST['minCreateTime']) ? $_POST['minCreateTime'] : '';

		$data = array();
		// $cache = new File();
		// if(!$data = @$cache->cacheData('article_themeDetail_cache')){
			$connect = Db::getInstance()->connect();
			//专题详情
			$sql = "SELECT [title],[description],[coverImg],[keywords] FROM [theme] WHERE [themeID]='" . $themeID . "' AND [isDel] = 0 ORDER BY [sort];";
			$result = sqlsrv_query($connect, $sql);
			if(!$result){
				sqlsrv_close($connect);
				return Response::show(200, "专题详情为空");
			}
			$row = sqlsrv_fetch_array($result);

			$data['themeID'] 	= $themeID;
			$data['title'] 		= $row['title'];
			$data['descrip'] 	= $row['description'];
			$data['coverImg'] 	= IMG_URL . $row['coverImg'];//专题封面只有一张
            if($row['coverImg'] != '' && strpos($row['coverImg'], 'http://') === false){
                $data['coverImg'] 	= IMG_URL . $row['coverImg'];
            }
			//推荐文章列表
			$kwArr = explode(",", $row['keywords']);
			$str = "";
			for($i=0,$len=count($kwArr);$i<$len;$i++){
				$str .= " Title LIKE '%" . $kwArr[$i] . "%' OR";
			}
			$str = rtrim($str, 'OR');
			$str = '(' . $str . ')';

			$sql2 = "SELECT TOP " . $pageSize . " [keywords], [Aid], [Title], [CoverImg], [Tag], [source], [ArticleType], [visit], [visitshow], [CreateTime]";
			if($uid != ''){
				$sql2 .= ", (SELECT COUNT(1) [iszan] FROM [articleInfo_Good] WHERE [articleInfo_Good].[Aid] = [ArticleInfo].[Aid] AND [articleInfo_Good].[Uid] = '" . $uid . "') AS 'iszan'";
			} else{
				$sql2 .= ", '0' AS 'iszan'";
			}
				$sql2 .= ", (SELECT COUNT(*) FROM [articleInfo_Good] WHERE [articleInfo_Good].[Aid] = [ArticleInfo].[Aid]) AS 'zan' FROM [ArticleInfo] WHERE";
            if($maxCreateTime == '' && $minCreateTime != ''){//最旧文章时间不为空
                $sql2 .= " DATEDIFF(SECOND,CONVERT(datetime,'" . date("Y-m-d H:i:s", $minCreateTime) . "',101), [CreateTime]) < 0 AND ";
            } elseif($maxCreateTime != '' && $minCreateTime == ''){//最新文章时间不为空
                $sql2 .= " DATEDIFF(SECOND,CONVERT(datetime,'" . date("Y-m-d H:i:s", $maxCreateTime) . "',101), [CreateTime]) > 0 AND ";
            }
			$sql2 .= " [isUP] = 1 AND [isDel] = 0 AND " . $str ." ORDER BY [CreateTime] DESC";
			//echo $sql2;die;
			$result2 = sqlsrv_query($connect, $sql2);
			if(!$result2){
				sqlsrv_close($connect);
				return Response::show(200, "推荐文章列表为空");
			}
			
			$i = 0;
			while($row2 = sqlsrv_fetch_array($result2)){
				$data['artList'][$i]['keywords'] 		= isset($row2['keywords']) ? $row2['keywords'] : '';
				$data['artList'][$i]['Aid'] 			= $row2['Aid'];
				$data['artList'][$i]['Title'] 			= $row2['Title'];
				//处理文章封面为多张的情况
				$coverImgArr = explode(',', $row2['CoverImg']);
				//var_dump($coverImgArr);die;
				$data['artList'][$i]['CoverImg'] 		= $coverImgArr[0];
				//for($j=0,$len=count($coverImgArr);$j<$len;$j++){
				    if($coverImgArr[0] != '' && strpos($coverImgArr[0], 'http://') === false){
                        $data['artList'][$i]['CoverImg']  = IMG_URL . $coverImgArr[0];
                    }
				//}
				//$data['artList'][$i]['CoverImg'] 		= rtrim($data[$i]['CoverImg'], ',');

				$data['artList'][$i]['Tag'] 			= $row2['Tag'];
				$data['artList'][$i]['source']		 	= $row2['source'];
				$data['artList'][$i]['ArticleType'] 	= $row2['ArticleType'];
				$data['artList'][$i]['visit'] 			= $row2['visit'];
				$data['artList'][$i]['visitshow'] 		= $row2['visitshow'];	
				$data['artList'][$i]['CreateTime'] 		= strtotime($row2['CreateTime']->format("Y-m-d H:i:s"));	
				$data['artList'][$i]['isZan'] 			= $row2['iszan']; 
				$data['artList'][$i]['zan'] 			= $row2['zan'];
				$i++;
			}
			//专题文章列表
			$sql3  = "SELECT TOP 5 [themeAid], [themeTitle], [coverImg], [message] ";
			$sql3 .= "FROM [themeArticle] WHERE [themeId]='" . $themeID . "' AND ";
			$sql3 .= "[isUp] = 1 AND [isDel] = 0 ORDER BY [createTime] DESC";
			$result3 = sqlsrv_query($connect, $sql3);
			if(!$result3){
				sqlsrv_close($connect);
				return Response::show(200, "专题文章列表为空");
			}

			$j = 0;
			while($row3 = sqlsrv_fetch_array($result3)){
				$data['themeArtList'][$j]['themeAid'] 		= $row3['themeAid'];
				$data['themeArtList'][$j]['themeTitle'] 	= $row3['themeTitle'];
				$data['themeArtList'][$j]['coverImg'] 		= $row3['coverImg'];
                if($row3['coverImg'] != '' && strpos($row3['coverImg'], 'http://') === false){
                    $data['themeArtList'][$j]['coverImg'] 		= IMG_URL . $row3['coverImg'];
                }
				$messageArr = json_decode($row3['message'], true);
				$data['themeArtList'][$j]['firstPart'] 		= $messageArr[0]['content'];
				$j++;
			}
			sqlsrv_close($connect);
		// 	$cache->cacheData('article_themeDetail_cache', $data, 3600*24);
		// }
		if($data){
			return Response::show(200, "获取专题详情、专题文章列表、推荐文章成功", $data);
		} else{
			return Response::show(200, "专题详情、专题文章列表、推荐文章为空");
		}
	}

    /**
     * 专题文章列表（点击 专题详情--专题文章列表--更多 之后显示）
     * @return string
     */
	public function themeArtList(){
		// $uid 			= isset($_POST['uid']) ? $_POST['uid'] : '';
		$themeId 		= isset($_POST['themeId']) ? $_POST['themeId'] : '';
		$pageSize 		= isset($_POST['pageSize']) ? $_POST['pageSize'] : 5;
		$maxCreateTime 	= isset($_POST['maxCreateTime']) ? $_POST['maxCreateTime'] : '';//最新文章时间不为空，用于刷新，可能为空
		$minCreateTime 	= isset($_POST['minCreateTime']) ? $_POST['minCreateTime'] : '';//最旧文章时间不为空，用于加载

		$connect = Db::getInstance()->connect();
		$sql  = "SELECT TOP " . $pageSize . " [themeAid], [themeTitle], [coverImg], [message], [createTime], [visit], [visitShow]";
		$sql .= "FROM [themeArticle] WHERE [themeId] = '" . $themeId . "' AND ";
		if($maxCreateTime == '' && $minCreateTime != ''){//最旧文章时间不为空
			$sql .= "DATEDIFF(SECOND,CONVERT(datetime,'" . date("Y-m-d H:i:s", $minCreateTime) . "',101), [CreateTime]) < 0 AND ";
		} elseif($maxCreateTime != '' && $minCreateTime == ''){//最新文章时间不为空
			$sql .= "DATEDIFF(SECOND,CONVERT(datetime,'" . date("Y-m-d H:i:s", $maxCreateTime) . "',101), [CreateTime]) > 0 AND ";
		}
		$sql .= "[isUp] = 1 AND [isDel] = 0 ORDER BY [createTime] DESC";
		// selfEcho($sql);
		$result = sqlsrv_query($connect, $sql);
		if(!$result){
			sqlsrv_close($connect);
			return Response::show(200, "专题文章列表为空");
		}

		$data = array();
		$i = 0;
		while($row = sqlsrv_fetch_array($result)){
			$data[$i]['themeAid'] 		= $row['themeAid'];
			$data[$i]['themeTitle'] 	= $row['themeTitle'];
			$data[$i]['coverImg'] 		= $row['coverImg'];
			
			$messageArr = json_decode($row['message'], true);
			$data[$i]['firstPart'] 		= $messageArr[0]['content'];

			$data[$i]['visit']			= $row['visit'];	
			$data[$i]['visitShow']		= $row['visitShow'];	
			$data[$i]['createTime'] 	= strtotime($row['createTime']->format("Y-m-d H:i:s"));	
			$i++;
		}
		sqlsrv_close($connect);
		// selfDump($data);
		if($data){
			return Response::show(200, "获取专题文章列表成功", $data);
		} else{
			return Response::show(200, "专题文章列表为空");
		}
	}

    /**
     * 专题文章详情
     * @return string
     */
	public function themeArtDetail(){
		$themeAid = isset($_POST['themeAid']) ? $_POST['themeAid'] : '';

		$data = array();
		// $cache = new File();
		// if(!$data = @$cache->cacheData('article_themeDetail_cache')){
			$connect = Db::getInstance()->connect();
			$sql = "SELECT TOP 1 [themeTitle],[foreword],[isCom],[message],[createTime],[type],[planner] FROM [themeArticle] WHERE [themeAid]='" . $themeAid ."'";
			// selfEcho($sql);
			$result = sqlsrv_query($connect, $sql);
			if(!$result){
				sqlsrv_close($connect);
				return Response::show(200, "专题文章为空");
			}
			$row = sqlsrv_fetch_array($result);

			$data['themeTitle'] = $row['themeTitle'];
			$data['foreword'] 	= $row['foreword'];
			$data['isCom'] 		= $row['isCom'];
			if(!empty($row['createTime'])){
                $data['createTime'] = strtotime($row['createTime']->format("Y-m-d H:i:s"));
            } else{
                $data['createTime'] = '';
            }

			$data['type'] 		= $row['type'];
			$data['planner'] 	= $row['planner'];

			$de_json = json_decode($row['message'],TRUE);
    		$len = count($de_json);
	        for($i=0;$i<$len;$i++){
	            if($de_json[$i]['image'] != '' && strpos($de_json[$i]['image'], 'http://') === false){
                    $de_json[$i]['image'] 	= IMG_URL . $de_json[$i]['image'];
                }
	       	}
	       	$data['coverImg'] =  $de_json[0]['image'];
	        $data['message']		= $de_json;
			sqlsrv_close($connect);
		// 	$cache->cacheData('article_themeDetail_cache', $data, 3600*24);
		// }

		if($data){
			return Response::show(200, "获取专题文章成功", $data);
		} else{
			return Response::show(200, "专题文章为空");
		}
	}

}
