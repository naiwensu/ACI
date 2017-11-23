<!doctype html>
<html>
<head>
<meta charset="UTF-8" />
<title><?php echo $Title; ?> - <?php echo $Powered; ?></title>
<link rel="stylesheet" href="../css/install.css?v=9.0" />
</head>
<body>
<div class="wrap">
  <div class="header">
    <h1 class="logo">logo</h1>
    <div class="icon_install">安装向导</div>
    <div class="version"></div>
  </div>
  <div class="section">
    <div class="main cc" align="center">
    	
       <?php
        $html = '';
		switch ( $action ) {
			case '1':
			$html = "<textarea rows='25' cols='95' >".$textArea."</textarea>";
			break;

			case '2':
			$html = "<iframe height='400px' width='700px' src='".$htmlPath."'></iframe>";
			break;
		}
		echo $html;
       ?>

	
    </div>
    <div class="bottom tac"> <a href="<?php echo $_SERVER['PHP_SELF']; ?>?step=2" class="btn">接 受</a> </div>
  </div>
</div>
<div class="footer"> &copy; 2014-2016 <a href="http://www.tp-shop.cn" target="_blank">www.tp-shop.cn</a> 深圳搜豹网络出品</div>
</body>
</html>