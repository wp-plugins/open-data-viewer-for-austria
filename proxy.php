<?php
require( '../../../wp-load.php' );


// this proxy is needed because to get the ckan data from a foreign server
$url = urldecode(($_GET['url']));
$url = str_replace(" ","%20",$url);
if (strpos($url, "pop") !== FALSE) return ""; // http://www.heise.de/newsticker/meldung/cURL-auf-Abwegen-1800433.html

$output = wp_remote_retrieve_body(wp_remote_get($url, array("user-agent" => "Mozilla/5.0 (Windows NT 6.1; rv:17.0) Gecko/20100101 Firefox/17.0"))); // using a common useragent to prevent blocking from some server

// this code converts to utf-8 when needed. But mb_check_encoding is critical code! http://de2.php.net/manual/de/function.mb-check-encoding.php
if(!mb_check_encoding($output, 'UTF-8')) { // only convert when string is not utf-8
    echo utf8_encode($output);
} else {
    echo $output;
}

?>