<head><link rel="stylesheet" type="text/css" href="../css/style.css"></head>
<?php if (isset($_GET["res"]) == 'ok') { ?>
    <div class="buttons">
        <input type="button" onclick="window.location='index.php'" value="生成完毕"/>
    </div>
<?php }else{ ?>
    <div class="buttons">
        <form action="" method="post">
            <input type="submit" name="submit" value="一键生成"/>
        </form>
    </div>
<?php } ?>

<?php
    include "TopSdk.php";
    require_once ('config.php');
    date_default_timezone_set('Asia/Shanghai');
    error_reporting(E_ALL);

    $c = new TopClient;
    $c->appkey = '24338691';
    $c->secretKey = '1cef1ccc00e1e110e6859bfa3b6d7ae5';
    $c->format = 'json';
    $reqs = new TbkUatmFavoritesGetRequest;
    $reqs->setPageNo("1");
    $reqs->setPageSize("30");
    $reqs->setFields("favorites_title,favorites_id");
    $reqs->setType("1");
    $resps = $c->execute($reqs);

    if (isset($_POST["submit"])) {
        foreach ($resps->results->tbk_favorites as $list) {
            $req = new TbkUatmFavoritesItemGetRequest;
            $req->setPageSize("50");
            $req->setAdzoneId("111218323");
            $req->setFavoritesId("".$list->favorites_id."");
            $req->setFields("title,pict_url,reserve_price,volume,zk_final_price,user_type,click_url,coupon_click_url,commission_rate,coupon_info,coupon_total_count,coupon_remain_count");
            $resp = $c->execute($req);
            $title = explode("-", $list->favorites_title);
            $filename = $title[0].'-'.date("Y-m-d").'.md';
            if($title[0] == "首页"){ $show = "index = \"添加这一行将显示在首页\"".PHP_EOL.""; }else{ $show = "series = [\"".$title[0]."\"]".PHP_EOL.""; }
            file_put_contents( $filename, "+++".PHP_EOL."title = \"".$title[1]."\"".PHP_EOL."date = \"".date("Y-m-d")."\"".PHP_EOL."".$show."+++".PHP_EOL.PHP_EOL."<ul class=\"pro_detail\">".PHP_EOL."" );
            foreach ($resp->results->uatm_tbk_item as $item) {
                $coupon = 0;
                $ccurl = $item->click_url;
                $zk_price = '原价¥'.$item->reserve_price;
                if ($item->coupon_click_url != null) {
                    preg_match_all('/\d+/', $item->coupon_info, $arr);
                    $coupon = $arr[0][1]==null?$arr[0][0]:$arr[0][1];
                    $ccurl = $item->coupon_click_url;
                    $zk_price = '现价¥'.$item->zk_final_price;
                }
                if ($item->user_type == 0){
                    $pt = "taobao"; $ptcn = "淘宝";
                }else {
                    $pt = "tmall"; $ptcn = "天猫";
                }
                $str = $item->zk_final_price-$coupon;
                $decs = explode(".", $str);
                $dec = $decs[1]>0?'.'.$decs[1]:'.0';
                $dcurl = $ccurl.'-'.date("Y-m-d");
                $surl = shorturl($dcurl);
                $result = mysqli_query($conn, "select * from shorturl where shorturl_id='".$surl."'")or die(mysqli_error($conn));
                $snum = mysqli_num_rows($result);
                if($snum == 0){
                    mysqli_query($conn, "insert into shorturl (shorturl_id, shorturl_title, shorturl_img, shorturl_url) values('".$surl."','".$item->title."','".$item->pict_url."','".$ccurl."')")or die(mysqli_error($conn));
                }
                file_put_contents($filename,"<li class=\"pro_detail_to\">".PHP_EOL."<div class=\"zk-item\">".PHP_EOL."<div class=\"img-area\">".PHP_EOL."<a class=\"alink\" target=\"_blank\" href=\"http://fmego.com/code.php?cv=".$surl."\">".PHP_EOL."", FILE_APPEND);
                if ($item->coupon_click_url != null){ 
                    file_put_contents($filename,"<div class=\"lq\">".PHP_EOL."<div class=\"lq-t\">".PHP_EOL."<span class=\"lq-t-d1\">领优惠券</span>".PHP_EOL."<span class=\"lq-t-d2\">省<em>".
                    "".$coupon."</em>元</span>".PHP_EOL."</div>".PHP_EOL."<div class=\"lq-b\"></div>".PHP_EOL."</div>".PHP_EOL."", FILE_APPEND);
                }
                file_put_contents($filename,"<img class=\"swiper-lazy\" data-src=\"".$item->pict_url."\">".PHP_EOL."</a>".PHP_EOL."</div>".PHP_EOL."".
                "<p class=\"title-area item\"><span class=\"post-free\">包邮</span>".$item->title."</p>".PHP_EOL."<div class=\"raw-price-area\">".$zk_price."", FILE_APPEND);
                if ($item->coupon_click_url != null){
                    file_put_contents($filename, "<p class=\"sold item\">已领".($item->coupon_total_count-$item->coupon_remain_count)."张券</p>", FILE_APPEND);
                }else{
                    file_put_contents($filename, "<p class=\"sold item\">已售".$item->volume."笔</p>", FILE_APPEND);
                }
                file_put_contents($filename, "</div>".PHP_EOL."<div class=\"info\">".PHP_EOL."<div class=\"price-area\">".PHP_EOL."<span class=\"price\">¥</span><em class=\"number-font\">".$decs[0]."".
                "</em><em class=\"decimal\">".$dec."</em>".PHP_EOL."", FILE_APPEND);
                if ($item->coupon_click_url != null){
                    file_put_contents($filename, "<i style=\"background: url(../../img/juanhoujia.png) center no-repeat;\"></i><span class=\"volume-price\">卷后价</span>".PHP_EOL."</div>".PHP_EOL."", FILE_APPEND);
                }else{
                    file_put_contents($filename, "<i style=\"background: url(../../img/zhehoujia.png) center no-repeat;\"></i><span class=\"volume-price\">折后价</span>".PHP_EOL."</div>".PHP_EOL."", FILE_APPEND);
                }
                if ($item->coupon_click_url != null){
                    file_put_contents($filename, "<div class=\"buy-area\">".PHP_EOL."<a rel=\"nofollow\" target=\"_blank\" href=\"http://fmego.com/code.php?cv=".$surl."\">".PHP_EOL."<span class=\"coupon-amount\">".$ptcn."</span>".PHP_EOL."".
                    "<span class=\"btn-title\">去领券</span>".PHP_EOL."</a>".PHP_EOL."</div>".PHP_EOL."", FILE_APPEND);
                }
                file_put_contents($filename, "<div class=\"platform-area\"><span>".$ptcn."</span><img class=\"swiper-lazy\" data-src=\"../../img/".$pt.".png\"></div>".PHP_EOL."</div>".PHP_EOL."</div>".PHP_EOL."</li>".PHP_EOL."", FILE_APPEND);
            }
            file_put_contents($filename, "</ul>", FILE_APPEND);
        }
        header("Location:index.php?res=ok");
        exit;
    }
?>