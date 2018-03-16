<?php
require_once "./frame/response.php";
require_once "./frame/db.php";
require_once "./frame/file.php";

class IndexController{
	public function index(){
		return Response::show(400, '请输入参数');
	}
}