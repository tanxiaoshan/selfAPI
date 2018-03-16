<?php
require_once "../Conn/Conn.php";

$id = $_GET['id'];
if(strlen($id) > 0){
    $connect = connect();
    $info = $info2 = array();
    $sql = "SELECT TOP 1 [Title],[CoverImg],[Message],
            [Editor] = (SELECT TOP 1 [NickName] FROM [dbo].[UserInfo] WHERE [Uid] = [dbo].[ArticleInfo].[Uid]),[CreateTime],[visitshow] 
            FROM [dbo].[ArticleInfo] WHERE [Aid] = '" . $id . "' AND [isDel] = 0";
    $result = sqlsrv_query($connect, $sql);
    $info = sqlsrv_fetch_array($result);

    $sql2 = "Select TOP 10 * From [votedList] WHERE [isDel] = 0 AND [review] = 1 ORDER BY [id] desc";
    $result2 = sqlsrv_query($connect, $sql2);
    while($row2 = sqlsrv_fetch_array($result2)){
	$info2[] = $row2;
    }
    sqlsrv_close($connect);
}
?>

<!doctype html>
<html>
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="description" content="<?php echo $info['Title'];?>">
	<meta name="shareicon" content="<?php echo $info['CoverImg'];?>">
	<title><?php echo $info['Title'];?></title>
	<link rel="stylesheet" type="text/css" href="http://app.financeun.com/News/Css/common.css">
	<link href="http://app.financeun.com/VOTE/Css/swiper.min.css" rel="stylesheet" type="text/css">
	<script type="text/javascript" src="http://app.financeun.com/News/Js/jquery-3.1.1.min.js"></script>
	<script type="text/javascript" src="http://app.financeun.com/News/Js/common.js"></script>
	<script src="http://app.financeun.com/VOTE/Js/swiper.jquery.min.js" type="text/javascript"></script>
	<style>
	.swiper-container {
		width: 100%;
		padding: 10px 0;
		overflow: hidden;
	}
	.swiper-slide {
		border: 1px solid #9c9b9b;
		background-position: center;
		background-size: cover;
		width: 250px !important;
		height: 120px !important;
	}
	.swiper-slide a {
		display: block;
		width: 100%;
		height: 100%;
	}
	</style>
</head>
<body>
<section>
    <div class="title"><?php echo $info['Title'];?></div>
    <div class="form"><?php echo $info['Editor'];?>.nbsp;.nbsp;.nbsp;.nbsp;<?php if(!empty($info['CreateTime'])){echo $info['CreateTime']->format('Y-m-d H:i:s');}?>.nbsp;.nbsp;.nbsp;.nbsp;.#37329;.#34701;.#65;.#80;.#80;.nbsp;.nbsp;.#x9605;.#x8BFB;.nbsp;<?php echo $info['visitshow'];?></div>
    <div class="section">
        <a href="http://a.app.qq.com/o/simple.jsp?pkgname=com.NationalPhotograpy.weishoot">
            <img src="http://weishoot.com/Content/story/images/ad1.jpg" width="100%">
        </a>
    </div>
    <?php
    $message = json_decode($info['Message'], true);
    $str  = "";
    for($i=0,$len=count($message);$i<$len;$i++){
        $str .= "<div class='section'>";
        if($message[$i]['picture']=="" .. $message[$i]['content']<>""){
            $str .= "<p class='text'>" . $message[$i]['content'] . "</p>";
        } else{
            $pic = $message[$i]['picture'];
            if(stripos($pic, "http://") === false){
                $pic = "http://img.financeun.com" . $pic;
            }
                $str .= "<img class='images' src='" . $pic . "'>";
            if($message[$i]['content'] <> ""){
                $str .= "<p>" . $message[$i]['content'] . "</p>";
            }
        }
        $str .= "</div>";
    }
    echo $str;
    ?>
    <div class="section">
        <a href="http://www.cgbchina.com.cn/">
            <img src="http://www.financeun.com/ad/gf.jpg" width="100%">
        </a><em class="adIcon"></em>
    </div>
    <?php
        if(is_array($info2)){
    ?>
    <div class="swiper-container">
        <div class="swiper-wrapper">
    <?php
        $str = "";
        for($i=0,$len=count($info2);$i<$len;$i++){
            $str .= '<div class="swiper-slide" style="background-image:url(../VOTE/Images/voteimg/' . $info2[$i]['titleimg'] . ');"><a href="http://app.financeun.com/vote/voted_' . $info2[$i]['id'] . '.html"></a></div>';
        }
        echo $str;
    ?>
        </div>
    </div>
    <?php
        }
    ?>
</section>
<div style="height:80px;">
</div>
<div class="downApp">
    <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAVAAAABKCAYAAADt0gyQAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyhpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNi1jMTM4IDc5LjE1OTgyNCwgMjAxNi8wOS8xNC0wMTowOTowMSAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIDIwMTcgKE1hY2ludG9zaCkiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6RkUyNDVDNkYzMkZEMTFFNzhDQjVDOEQ1MDMxRUY5QjMiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6RkUyNDVDNzAzMkZEMTFFNzhDQjVDOEQ1MDMxRUY5QjMiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDpGRTI0NUM2RDMyRkQxMUU3OENCNUM4RDUwMzFFRjlCMyIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDpGRTI0NUM2RTMyRkQxMUU3OENCNUM4RDUwMzFFRjlCMyIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PiHLe2gAACVcSURBVHja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" width="168">
    <a href="http://a.app.qq.com/o/simple.jsp?pkgname=com.financeun.finance">.#19979;.#36733;</a>
</div>
<script type="text/javascript">
var swiper = new Swiper('.swiper-container', {
    effect: 'coverflow',
    grabCursor: true,
    centeredSlides: true,
    slidesPerView: 'auto',
    coverflow: {
        rotate: 50,
        stretch: 0,
        depth: 10,
        modifier: 1,
        slideShadows : true
    }
});
</script>
</body>
</html>
