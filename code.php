<head>
<meta charset="utf-8">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no"/>
<link rel="stylesheet" type="text/css" href="../css/style.css">
</head>
<?php
    require_once ('config.php');
    if (isset($_GET["cv"])) {
        $result = mysqli_query($conn, "select * from shorturl where shorturl_id='".$_GET['cv']."'")or die(mysqli_error($conn));
        $snum = mysqli_num_rows($result);
        $lurl = mysqli_fetch_array($result);
        if ($snum > 0) {
            if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
                if (strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone') || strpos($_SERVER['HTTP_USER_AGENT'], 'iPad')) {
                    $brow = "Safari"; $icon = "safari";
                }else if (strpos($_SERVER['HTTP_USER_AGENT'], 'Android')) {
                    $brow = "淘宝或浏览器打开"; $icon = "browser";
                } ?>
                <div class="weixin">
                    <div class="prompt">
                        <div class="prompt-txt">
                            <span>点击右上角 选择<?php echo $brow;?></span>
                            <i style="background-image: url(../img/<?php echo $icon;?>.png);"></i>
                        </div>
                    </div>
                    <div class="item">
                        <div class="item-img">
                            <img src="<?php echo $lurl['shorturl_img'];?>">
                        </div>
                        <div class="item-title"><?php echo $lurl['shorturl_title'];?></div>
                    </div>
                </div> <?php
            }else {
                header("Location:".$lurl['shorturl_url']."");
                exit;
            }
        }else {
            echo "没有找到原始链接";
            exit;
        }
    }else {
        echo "无效链接";
        exit;
    }
?>