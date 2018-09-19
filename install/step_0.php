<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title><?php echo $html_title;?></title>
<link href="css/install.css" rel="stylesheet" type="text/css">
<link href="css/perfect-scrollbar.min.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="js/jquery.js"></script>
<script type="text/javascript" src="js/perfect-scrollbar.min.js"></script>
<script type="text/javascript" src="js/jquery.mousewheel.js"></script>
</head>
<body>
<?php echo $html_header;?>
<div class="main">
  <div class="text-box" id="text-box">
    <div class="license">
      <h1>系统安装协议</h1>
      <p>感谢您选择本系统。</p>
      <p>官方网址：http://www.dm299.com</p>
      <p>唯一网店：http://dm299.taobao.com</p>
      <p>QQ客服：123456</p>
      <p>QQ交流群：660343788</p>
    </div>
  </div>
  <div class="btn-box"><a href="index.php?step=1" class="btn btn-primary">同意协议进入安装</a><a href="javascript:window.close()" class="btn">不同意</a></div>
</div>
<?php echo $html_footer;?>
<script type="text/javascript">
$(document).ready(function(){
    //自定义滚定条
    $('#text-box').perfectScrollbar();
});
</script>
</body>
</html>
