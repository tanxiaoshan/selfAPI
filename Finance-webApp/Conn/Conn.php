<?php

$ua = $_SERVER['HTTP_USER_AGENT'];
if(strpos($ua, "financeunBrower=") !== false){
	$temp = explode("financeunBrower=", $ua);
	$userJson = $temp[1];
	$userArr = json_decode($userJson, true);
	$tokenkey = $userArr['tokenkey'];
	if($tokenkey <> null || $tokenkey <> ''){
		$connect = connect();
		$sql = "Select TOP 1 [Uid], [NickName] From [UserInfo] WHERE [UTokenkey] = '" . $tokenkey . "'";
		$result = sqlsrv_query($connect, $sql);
		$row = sqlsrv_fetch_array($result);
		$uid = $row['Uid'];
		$nickName = $row['NickName'];
		sqlsrv_close($connect);
	}
}

function connect(){
	$server = '192.168.0.50,217';
	$database = 'FinanceDatabase';
	$uid = 'sa';
	$pwd = '##*financeunUNPCN*-+|_';
	$connectInfo = array('Database'=>$database,
	                     'UID'=>$uid,
	                     'PWD'=>$pwd);
	return $connect = sqlsrv_connect($server, $connectInfo);
}