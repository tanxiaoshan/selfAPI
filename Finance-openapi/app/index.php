<?php
require_once "./frame/response.php";//入口文件加载控制器，以入口文件设置相对路径
require_once "./frame/db.php";
require_once $_SERVER['DOCUMENT_ROOT'] . '/FinanceDisk/Finance-openapi/tools/functions.php';
require_once "./frame/file.php";

/*error_reporting('display_errors', 'on');
error_reporting(E_ALL);
echo $b;*/
header('Access-Control-Allow-Origin:*');//very very very important!重要的事情重复三遍，这里非常重要，如果不设置，无法获得数据。

//定义 站点根目录、图片路径 为常量
define('BASE_URL', '/mnt/hgfs/share/www/FinanceDisk');
define('IMG_URL', 'http://img.financeun.com');

//token验证
$tokenKey = isset($_POST['tokenkey']) ? $_POST['tokenkey'] : '';
//echo $tokenKey;
try{
	$connect = Db::getInstance()->connect();
} catch(Exception $e){
	return Response::show(403, "数据库连接失败");
}
$sql = "select * from ThirdDevelop where TokenKey='" .$tokenKey . "'";
$result = sqlsrv_query($connect, $sql);
$row = sqlsrv_fetch_array($result);
//var_dump($row);
if(!$row){
	return Response::show(400, 'token验证失败'.$tokenKey);
}

$c = isset($_GET['c']) ? $_GET['c'] : "Index";
$a = isset($_GET['a']) ? $_GET['a'] : "index";

require_once './controller/' . $c . 'Controller.php';

$controller_name = $c . 'Controller';
$action_name = $a;
$ctrl = new $controller_name();
$ctrl->$action_name();

function selfDump($data){
	var_dump($data);
    die;
}

function selfEcho($data){
    echo $data;
    die;
}

