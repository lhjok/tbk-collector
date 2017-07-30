<?php
    if(!isset($_SESSION)){
        ob_start();
    }
    $hostname= "localhost"; //mysql地址
    $basename= "root"; //mysql用户名
    $basepass= "1026"; //mysql密码
    $database= "code"; //mysql数据库名称
    // 错误处理和时区设置
    error_reporting(E_ALL);
    ini_set('display_errors', TRUE);
    ini_set('display_startup_errors', TRUE);
    date_default_timezone_set('Asia/Shanghai');
    $conn= mysqli_connect($hostname, $basename, $basepass)or die("error!"); //连接mysql
    mysqli_select_db($conn, $database); //选择mysql数据库
    mysqli_query($conn, "set names 'utf8'")or die(mysqli_error($conn)); //mysql编码
    function shorturl($url) {
        $code = floatval(sprintf("%u", crc32($url)));
        $surl = '';
        while($code){
            $mod = fmod($code, 62);
            if($mod>9 && $mod<=35){
                $mod = chr($mod+55);
            }elseif($mod>35){
                $mod = chr($mod+61);
            }
            $surl .= $mod;
            $code = floor($code/62);
        }
        return $surl;
    }
?>