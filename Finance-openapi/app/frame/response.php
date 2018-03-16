<?php

class Response{
	/**
	 * 综合方式方式输出通信数据
	 * @param $code int 状态码
	 * @param $message string 提示信息
	 * @param $data array 数据
	 * @param $type string 数据类型
	 * @return string
	 */
	const JSON = 'json';
	public static function show($code, $message='', $data=array(), $type=self::JSON){
		if(!is_numeric($code)){
			return '';
		}
		$type = isset($_GET['format']) ? $_GET['format'] : self::JSON;
		$result = array(
			'code' 		=> $code,
			'message' 	=> $message,
			'data' 		=> $data
		);
		if($type == 'json'){
			self::json($code, $message, $data);
		} elseif($type == 'array'){
			var_dump($result);
			exit;
		} elseif($type == 'xml'){
			self::xmlEncode($code, $message, $data);
		} else{
			//ToDo
		}
	}

	/**
	 * 按json方式输出通信数据
	 * @param $code int 状态码
	 * @param $message string 提示信息
	 * @param $data array 数据
	 * @return string
	 */
	public static function json($code, $message='', $data=array()){
		if(!is_numeric($code)){
			return '';
		}
		$result = array(
			'code' 		=> $code,
			'message' 	=> $message,
			'data' 		=> $data
		);
		$str = json_encode($result);
		echo $str;
		//echo str_replace('\\\\','\\',$str);
		exit;
	}

	/**
	 * 按xml方式输出通信数据
	 * @param $code int 状态码
	 * @param $message string 提示信息
	 * @param $data array 数据
	 * @return string
	 */
	public static function xmlEncode($code, $message='', $data=array()){
		if(!is_numeric($code)){
			return '';
		}	
		$result = array(
			'code' 		=> $code,
			'message' 	=> $message,
			'data' 		=> $data
		);
		header("Content-Type:text/xml");
		$xml  = "<?xml version='1.0' encoding='UTF-8'?>\n";
		$xml .= "<root>\n";
		$xml .= self::xmlToEncode($result);
		$xml .= "</root>";
		echo $xml;
		exit;
	}
	public static function xmlToEncode($data){
		$xml = $attr = "";
		foreach($data as $key => $value){
			if(is_numeric($key)){
				$attr = "id='{$key}'";
				$key = "item";
			}
			$xml .= "<{$key} {$attr}>";
			$xml .= is_array($value) ? self::xmlToEncode($value) : $value;
			$xml .= "</{$key}>\n";
		}
		return $xml;
	}
}

/*$data = array(
	0 => "聚焦银行",
    1 => "金融市场",
    2 => "金融人物",
    3 => "金融办",
    4 => "城商行",
    5 => "农村金融",
    6 => "金融生态",
    7 => "金融案件",
    8 => "金融文化",
    9 => "基层金融",
    10 => "金融监管",
    11 => "图片",
    12 => "村镇银行",
    13 => "农商银行",
    14 => "金融研究",
    15 => null
);
Response::json(200, '获取数据成功', $data);*/

/*$data = array(
	0 => "聚焦银行",
    1 => "金融市场",
    2 => "金融人物",
    3 => "金融办",
    4 => "城商行",
    5 => "农村金融",
    6 => "金融生态",
    7 => "金融案件",
    8 => "金融文化",
    9 => "基层金融",
    10 => "金融监管",
    11 => "图片",
    12 => "村镇银行",
    13 => "农商银行",
    14 => "金融研究"
);
Response::xmlEncode(200, '获取数据成功', $data);*/

/*$data = array(
	0 => "聚焦银行",
    1 => "金融市场",
    2 => "金融人物",
    3 => "金融办",
    4 => "城商行",
    5 => "农村金融",
    6 => "金融生态",
    7 => "金融案件",
    8 => "金融文化",
    9 => "基层金融",
    10 => "金融监管",
    11 => "图片",
    12 => "村镇银行",
    13 => "农商银行",
    14 => "金融研究"
);
Response::show(200, '获取数据成功', $data, 'array');*/