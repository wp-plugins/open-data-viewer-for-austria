<?php
require( '../../../wp-load.php' );

// this proxy is needed because to get the ckan data from a foreign server
// The code checks if the requested url is in shortcode-data or widget-data
// If yes the request will be executed, if not =>'forbidden'
// If the user is logined the request will be also executed

$url = urldecode(($_GET['url']));
$nr = $_GET['id'];
$blnPost = $_GET['ispost'];
$urlCheck = $url;
$requestUrl = str_replace(" ","%20",$url);
if (strpos($url, "pop") !== FALSE) return ""; // http://www.heise.de/newsticker/meldung/cURL-auf-Abwegen-1800433.html

if (!is_user_logged_in()) {
    if (isset($nr)) {
        if ($blnPost === "1") {
               $post = get_post($nr, ARRAY_A);
               if ($post != null) {
                    $iPos = 0;
                    $content = $post["post_content"];
                    $iPos = strpos($content, htmlentities($urlCheck));
                    if ($iPos !== false) {
                        getAndReturn($requestUrl);
                    }
               }
        } else {
            $aryWidgetDatas = get_option('widget_CkanWidget');
            if ($aryWidgetDatas[$nr]["url"] == $urlCheck || $aryWidgetDatas[$nr]["metaurl"] == $urlCheck) {
                getAndReturn($requestUrl);
            }
        }
    }
} else {
    getAndReturn($url);
}
denied();



function getAndReturn($url) {
    $url = str_replace(" ","%20",$url);
    $response = wp_remote_get($url, array("user-agent" => "Mozilla/5.0 (Windows NT 6.1; rv:17.0) Gecko/20100101 Firefox/17.0", 'sslverify' => true, 'timeout' => '1200', "redirection" => 10 ));
    $output = wp_remote_retrieve_body($response); // using a common useragent to prevent blocking from some server
    
    // this code converts to utf-8 when needed. But mb_check_encoding is critical code! http://de2.php.net/manual/de/function.mb-check-encoding.php
    if(!mb_check_encoding($output, 'UTF-8')) { // only convert when string is not utf-8
        echo utf8_encode($output);
    } else {
        echo $output;
    }
    exit;
}

function denied() {
    header('HTTP/1.1 403 Forbidden');
    echo "Access denied";
    exit; 
}

?>