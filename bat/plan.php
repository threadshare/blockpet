<?php
ignore_user_abort (); // 关闭浏览器仍然执行
set_time_limit (0); // 让程序一直执行下去

$url="http://qkcw.x-g.vip/index.php/api/Plan/index";
$a=getHtml($url);

function getHtml($url, $data = null) {
	$curl = curl_init ();
	curl_setopt ( $curl, CURLOPT_URL, $url );
	curl_setopt ( $curl, CURLOPT_SSL_VERIFYPEER, FALSE );
	curl_setopt ( $curl, CURLOPT_SSL_VERIFYHOST, FALSE );
	if (! empty ( $data )) {
		curl_setopt ( $curl, CURLOPT_POST, 1 );
		curl_setopt ( $curl, CURLOPT_POSTFIELDS, $data );
	}
	curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt ( $curl, CURLOPT_TIMEOUT, 20 );
	$output = curl_exec ( $curl );
	curl_close ( $curl );
	return $output;
}
?>