<?php

require_once('../../../fluid.db.php');

function curl_get_contents($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);

    $output = curl_exec($ch);

    curl_close($ch);

    return $output;
}

$feed = curl_get_contents(YOUTUBE_FEED_URL);
$xml = new SimpleXmlElement($feed);

$xml_encode = json_encode($xml);
$f_feed['status'] = "ok";
$f_tmp = json_decode($xml_encode, TRUE);

$f = 0;
foreach($f_tmp['entry'] as $f_data) {
	if($f == $_GET['num'])
		break;
	else
		$f_feed['items']['entry'][] = $f_data;
	
	$f++;
}

$callback = $_GET['callback'];
header("Content-Type: application/json");
echo $callback . "(";
echo json_encode($f_feed);
echo ")";
?>
