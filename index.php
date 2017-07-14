<?php if (isset($_GET["res"]) == 'ok') { ?>
    <div style="position:absolute;width:200px;height:80px;top:0;right:0;bottom:0;left:0;margin:auto;">
        <input style="width:200px;height:80px;cursor:pointer;" type="button" onclick="window.location='index.php'" value="生成完毕"/>
    </div>
<?php }else{ ?>
    <div style="position:absolute;width:200px;height:80px;top:0;right:0;bottom:0;left:0;margin:auto;">
        <form action="" method="post" style="width:200px;height:80px;margin-bottom:0;">
            <input style="width:200px;height:80px;cursor:pointer;" type="submit" name="submit" value="一键生成"/>
        </form>
    </div>
<?php } ?>

<?php
    include "TopSdk.php";
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
            if($title[0] == "首页"){ $show = "index = \"添加这一行将显示在首页\"\n"; }else{ $show = "series = [\"".$title[0]."\"]\n"; }
            file_put_contents( $filename, "+++\ntitle = \"".$title[1]."\"\ndate = \"".date("Y-m-d")."\"\n".$show."+++\n\n<ul class=\"pro_detail\">\n" );
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
                file_put_contents($filename,"<li class=\"pro_detail_to\" style=\"margin: 0px 15px 16px 0px; padding: 7px 8px;\">".
                "\n<div class=\"zk-item\" style=\"width: 220px; height: 307px;\">".
                "\n<div class=\"img-area\" style=\"width: 220px; height: 220px;\">".
                "\n<a class=\"alink\" target=\"_blank\" href=\"".$ccurl."\" style=\"width: 220px; height: 220px;\">\n", FILE_APPEND);
                if ($item->coupon_click_url != null){ 
                    file_put_contents($filename,"<div class=\"lq\">\n<span class=\"lq-t-d1\">领优惠券</span>\n<span class=\"lq-t-d2\">省<em>".
                    "".$coupon."</em>元</span>\n</div>\n<div class=\"lq-b\"></div>\n", FILE_APPEND);
                }
                file_put_contents($filename,"<img class=\"swiper-lazy\" src=\"".$item->pict_url."\">\n</a>\n</div>".
                "\n<p class=\"title-area item\"><span class=\"post-free\">包邮</span>".$item->title."</p>\n<div class=\"raw-price-area\">".$zk_price."\n", FILE_APPEND);
                if ($item->coupon_click_url != null){
                    file_put_contents($filename, "<p class=\"sold item\">已领".($item->coupon_total_count-$item->coupon_remain_count)."张券</p>\n", FILE_APPEND);
                }else{
                    file_put_contents($filename, "<p class=\"sold item\">已售".$item->volume."笔</p>\n", FILE_APPEND);
                }
                file_put_contents($filename, "</div>\n<div class=\"info\">\n<div class=\"price-area\">\n<span class=\"price\">¥</span><em class=\"number-font\">".$decs[0]."".
                "</em><em class=\"decimal\">".$dec."</em>\n", FILE_APPEND);
                if ($item->coupon_click_url != null){
                    file_put_contents($filename, "<i style=\"background: url(../../img/juanhoujia.png) center no-repeat;\"></i><span class=\"volume-price\">卷后价</span>\n</div>\n", FILE_APPEND);
                }else{
                    file_put_contents($filename, "<i style=\"background: url(../../img/zhehoujia.png) center no-repeat;\"></i><span class=\"volume-price\">折后价</span>\n</div>\n", FILE_APPEND);
                }
                if ($item->coupon_click_url != null){
                    file_put_contents($filename, "<div class=\"buy-area\">\n<a rel=\"nofollow\" target=\"_blank\" href=\"".$ccurl."\">\n<span class=\"coupon-amount\">".$ptcn."</span>\n".
                    "<span class=\"btn-title\">去领券</span>\n</a>\n</div>\n", FILE_APPEND);
                }
                file_put_contents($filename, "<div class=\"platform-area\"><span>".$ptcn."</span><img class=\"swiper-lazy\" src=\"../../img/".$pt.".png\"></div>\n</div>\n</div>\n</li>\n", FILE_APPEND);
            }
            file_put_contents($filename, "</ul>", FILE_APPEND);
        }
        header("Location:index.php?res=ok");
        exit;
    }
?>