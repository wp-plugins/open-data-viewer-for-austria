<?php
// this proxy is needed because to get the ckan data from a foreign server
$url = urldecode(($_GET['url']));
$url = str_replace(" ","%20",$url);
if (strpos($url, "pop") !== FALSE) return ""; // http://www.heise.de/newsticker/meldung/cURL-auf-Abwegen-1800433.html
$ch = curl_init();
// URL to grab
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
$output = curl_exec($ch);
curl_close($ch);

// this code converts to utf-8 when needed. But mb_check_encoding is critical code! http://de2.php.net/manual/de/function.mb-check-encoding.php
if(!mb_check_encoding($output, 'UTF-8')) { // only convert when string is not utf-8
    echo utf8_encode($output);
} else {
    echo $output;
}

?>