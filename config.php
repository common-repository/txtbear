<?php

define('TXTBEAR_VERSION', '1.1.2223.2113');

function get($strUrl) {
	$resCurl = curl_init($strUrl);
	curl_setopt($resCurl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($resCurl, CURLOPT_HEADER, 0);
	$strData = curl_exec($resCurl);
	$intStatus = curl_getinfo($resCurl, CURLINFO_HTTP_CODE);
	return array('status' => $intStatus, 'data' => $strData);
}

function txtbear_admin_is_german() {
	return in_array(get_option('txtbear_admin_country'), explode(' ', 'DE AT CH LI'));
}
