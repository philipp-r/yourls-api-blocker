<?php
/*
Plugin Name: API blocker
Plugin URI: https://github.com/philipp-r/yourls-api-blocker
Description: Block actions ('url-stats', 'stats', 'db-stats') and GET requests and HTTPS only
Version: 1.0
Author: philipp-r
Author URI: https://github.com/philipp-r
*/


// No direct call (source https://github.com/seandrickson/YOURLS-Disable-JSONP/blob/62c74076e5396a936eb63887ab70c0c2b7e72acb/disable-jsonp/plugin.php)
if( !defined( 'YOURLS_ABSPATH' ) ) die();

/*
// Whitelist example.com Domain (https://github.com/Panthro/YourlsWhitelistDomains)
// Hook the custom function into the 'pre_check_domain_flood' event
yourls_add_filter( 'shunt_add_new_link', 'panthro_whitelist_domain_root' );
// Get whitelisted domains from YOURLS options feature and compare with current domain address
function panthro_whitelist_domain_root ( $bol, $url ) {
	$parse = parse_url($url);
	$domain = str_ireplace('www.', '', parse_url($url, PHP_URL_HOST));
	$return = false;
	$domain_list = yourls_get_option ('panthro_whitelist_domain_list');
	if ( $domain_list ) {
		// whitelisted Domains
		$domain_list_display = array(
			"example.com",
			"dev.example.com",
			"blog.example.com",
			"status.example.com",
		);
		if (!in_array($domain, $domain_list_display)) {
		// if (strpos($domain_list_display,$domain) === false) {
			$return['status']    = 'fail';
			$return['code']      = 'error:domain-not-allowed';
			$return['message']   = 'This domain is not allowed';
			$return['errorCode'] = '400';
		}
	}
	return $return;
}
*/


// processed before output
yourls_add_action( 'pre_api_output', 'blocker_pre_api_output' );
function blocker_pre_api_output( $argh ) { 

	// reset all GET parameters since we only accept POST
	$_GET = array();
	
	// allow only HTTPS connection
	if( $_SERVER['HTTPS'] != "on" ){
		header("HTTP/1.1 403 Forbidden");
		if( strtolower($_POST["format"]) == "xml" ){
			header('Content-Type: application/xml; charset=utf-8');
			echo '<?xml version="1.0" encoding="iso-8859-1"?><result><errorCode>403</errorCode><message>Only HTTPS allowed</message></result>';
		}else{
			header('Content-Type: application/json; charset=utf-8');
			echo '{"errorCode":403,"message":"Only HTTPS allowed"}';
		}
		die();
	}

	// block requests without timestamp
	if( empty($_POST["timestamp"]) ){
		header("HTTP/1.1 403 Forbidden");
		if( strtolower($_POST["format"]) == "xml" ){
			header('Content-Type: application/xml; charset=utf-8');
			echo '<?xml version="1.0" encoding="iso-8859-1"?><result><errorCode>403</errorCode><message>Authentication with time limited signature token required</message></result>';
		}else{
			header('Content-Type: application/json; charset=utf-8');
			echo '{"errorCode":403,"message":"Authentication with time limited signature token required"}';
		}
		die();
	}

	// block actions ('url-stats', 'stats', 'db-stats')
	elseif( strtolower($_POST["action"]) == "url-stats" ||
			strtolower($_POST["action"]) == "stats" ||
			strtolower($_POST["action"]) == "db-stats"  ){ 
		header("HTTP/1.1 400 Bad Request");
		if( strtolower($_POST["format"]) == "xml" ){
			header('Content-Type: application/xml; charset=utf-8');
			echo '<?xml version="1.0" encoding="iso-8859-1"?><result><errorCode>400</errorCode><message>Invalid action requested</message></result>';
		}else{
			header('Content-Type: application/json; charset=utf-8');
			echo '{"errorCode":400,"message":"Invalid action requested"}';
		}
		die();
	}

}



// Formats have to be blocked here again that successful requests are not processed
yourls_add_action( 'api', 'blocker_api' );
function blocker_api( $argh ) { 
	
	// reset all GET parameters since we only accept POST
	$_GET = array();

	// allow only HTTPS connection
	if( $_SERVER['HTTPS'] != "on" ){
		header("HTTP/1.1 403 Forbidden");
		if( strtolower($_POST["format"]) == "xml" ){
			header('Content-Type: application/xml; charset=utf-8');
			echo '<?xml version="1.0" encoding="iso-8859-1"?><result><errorCode>403</errorCode><message>Only HTTPS allowed</message></result>';
		}else{
			header('Content-Type: application/json; charset=utf-8');
			echo '{"errorCode":403,"message":"Only HTTPS allowed"}';
		}
		die();
	}
	
	// block api execution for requests without timestamp
	if( empty($_POST["timestamp"]) ){
		header("HTTP/1.1 403 Forbidden");
		if( strtolower($_POST["format"]) == "xml" ){
			header('Content-Type: application/xml; charset=utf-8');
			echo '<?xml version="1.0" encoding="iso-8859-1"?><result><errorCode>403</errorCode><message>Authentication with time limited signature token required</message></result>';
		}else{
			header('Content-Type: application/json; charset=utf-8');
			echo '{"errorCode":403,"message":"Authentication with time limited signature token required"}';
		}
			die();
	}



}
