<?php
/**
 * Plugin Name: WP Parallel Loader
 * Plugin URI: http://www.bumpnetworks.com
 * Description: WP Parallel Loader
 * Version: 1.0.1
 * Author: Vladimir Zabara
 */

include ('wp-parallel-loader.class.php');
if ( is_admin () ) {
	include ('wp-parallel-loader-admin.class.php');
	$wppl = new ParallelLoaderAdmin;
    if ($domain = $wppl->get_option('wp-parallel-loader-repository')) {
        require 'plugin-update-checker/plugin-update-checker.php';
        $myUpdateChecker = PucFactory::buildUpdateChecker(
            rtrim($domain) . '/index.php?action=get_metadata&slug=' . pathinfo(__FILE__, PATHINFO_FILENAME),
            __FILE__
        );
    }
} else {
	$wppl = new ParallelLoader;
	add_filter('get_header', 'wppl_buffer_start');
	add_filter('get_footer', 'wppl_buffer_end');
}

register_deactivation_hook( __FILE__, array( $wppl, 'plugin_deactivation' ) );

function wppl_callback($buffer) {
	global $wppl;
	if ($wppl->get_option('wp-parallel-loader-images')) {
		$buffer = wppl_rewriteImgUrls($buffer);
		$buffer = wppl_rewriteCssUrls($buffer);
	}
	if ($wppl->get_option('wp-parallel-loader-scripts')) {
		$buffer = wppl_rewriteScriptTags($buffer);
	}
	if ($wppl->get_option('wp-parallel-loader-css')) {
		$buffer = wppl_rewriteLinkTags($buffer);
	}
	return $buffer;
}

function wppl_buffer_start() { ob_start('wppl_callback'); }

function wppl_buffer_end() { ob_get_contents(); }

function wppl_rewriteCssUrls($css) {
	global $wppl;
	$cdns = $wppl->getCDNs();
	$pattern = "/url\(\s*[\"']?([^\s\"']+?)[\"']?\s*\)/";
	return preg_replace_callback(
		$pattern,
		function ($match) use ($cdns) {
			// try to exclude template patterns like ${variable}
			if (substr($match[3], 0, 2)!='//' && !preg_match('/{\$+(.*?)}|\${+(.*?)}|{{+(.*?)}}/', $match[3])) {
				if (strtolower(substr($match[1], 0, 4))=='http') {
					$parts = parse_url($match[1]);
					if ($parts['host']==$_SERVER['SERVER_NAME']) {
						$cdnParts = parse_url($cdns[abs(crc32($match[1]) % count($cdns))]);
						$parts['host'] = $cdnParts['host'];
						$resource = wppl_unparse_url($parts);
					} else {
						$resource = $match[1];
					}
				} else {
					$resource = $cdns[abs(crc32($match[1]) % count($cdns))].$match[1];
				}
				if (wppl_getProtocol()=='https') {
					$resource = str_ireplace('http:', 'https:', $resource);
				}
				return 'url('.$resource.')';
			}
			return $match[0];
		},
		$css
	);
}

function wppl_rewriteImgUrls($html) {
	global $wppl;
	$cdns = $wppl->getCDNs();
	// look for IMG tags only
	$pattern = "/(<img[^>]*?)(src=[\"']?([^\"']*)[\"']?)([^>]*>)/";
	return preg_replace_callback(
		$pattern,
		function ($match) use ($pattern, $cdns) {
			// try to exclude template patterns like ${variable}
			if (substr($match[3], 0, 2)!='//' && !preg_match('/{\$+(.*?)}|\${+(.*?)}|{{+(.*?)}}/', $match[3])) {
				if (strtolower(substr($match[3], 0, 4))=='http') {
					$parts = parse_url($match[3]);
					if ($parts['host']==$_SERVER['SERVER_NAME']) {
						$cdnParts = parse_url($cdns[abs(crc32($match[3]) % count($cdns))]);
						$parts['host'] = $cdnParts['host'];
						$resource = wppl_unparse_url($parts);
					} else {
						$resource = $match[3];
					}
				} else {
					$resource = $cdns[abs(crc32($match[3]) % count($cdns))].$match[3];
				}
				if (wppl_getProtocol()=='https') {
					$resource = str_ireplace('http:', 'https:', $resource);
				}
				return preg_replace($pattern, '$1src="'.$resource.'"$4', $match[0]);
			}
			return $match[0];
		},
		$html
	);
}

function wppl_rewriteScriptTags($html) {
	global $wppl;
	$cdns = $wppl->getCDNs();
	// look for SCRIPT tags only
	$pattern = "/(<script[^>]*?)(src=[\"']?([^\"']*)[\"']?)([^>]*>)/";
	return preg_replace_callback(
		$pattern,
		function ($match) use ($pattern, $cdns) {
			// try to exclude template patterns like ${variable}
			if (substr($match[3], 0, 2)!='//' && !preg_match('/{\$+(.*?)}|\${+(.*?)}|{{+(.*?)}}/', $match[3])) {
				if (strtolower(substr($match[3], 0, 4))=='http') {
					$parts = parse_url($match[3]);
					if ($parts['host']==$_SERVER['SERVER_NAME']) {
						$cdnParts = parse_url($cdns[abs(crc32($match[3]) % count($cdns))]);
						$parts['host'] = $cdnParts['host'];
						$resource = wppl_unparse_url($parts);
					} else {
						$resource = $match[3];
					}
				} else {
					$resource = $cdns[abs(crc32($match[3]) % count($cdns))].$match[3];
				}
				if (wppl_getProtocol()=='https') {
					$resource = str_ireplace('http:', 'https:', $resource);
				}
				return preg_replace($pattern, '$1src="'.$resource.'"$4', $match[0]);
			}
			return $match[0];
		},
		$html
	);
}

function wppl_rewriteLinkTags($html) {
	global $wppl;
	$cdns = $wppl->getCDNs();
	// look for LINK tags w/ type=text/css attribute installed
	$pattern = "/(<link[^>]*?)(href=[\"']?([^\"']*)[\"']?)([^>]*>)/";
	return preg_replace_callback(
		$pattern,
		function ($match) use ($pattern, $cdns) {
			// try to exclude template patterns like ${variable}
			// and look for rel=stylesheet or type=text/css only
			if (substr($match[3], 0, 2)!='//' && !preg_match('/{\$+(.*?)}|\${+(.*?)}|{{+(.*?)}}/', $match[3]) && preg_match("/rel=[\"']?stylesheet[\"']?|type=[\"']?text\/css[\"']?/", $match[0])) {
				if (strtolower(substr($match[3], 0, 4))=='http') {
					$parts = parse_url($match[3]);
					if ($parts['host']==$_SERVER['SERVER_NAME']) {
						$cdnParts = parse_url($cdns[abs(crc32($match[3]) % count($cdns))]);
						$parts['host'] = $cdnParts['host'];
						$resource = wppl_unparse_url($parts);
					} else {
						$resource = $match[3];
					}
				} else {
					$resource = $cdns[abs(crc32($match[3]) % count($cdns))].$match[3];
				}
				if (wppl_getProtocol()=='https') {
					$resource = str_ireplace('http:', 'https:', $resource);
				}
				return preg_replace($pattern, '$1href="'.$resource.'"$4', $match[0]);
			}
			return $match[0];
		},
		$html
	);
}

function wppl_getProtocol() {
	$protocol = "http";
	if (isset($_SERVER['HTTPS']) && 'on' == strtolower($_SERVER['HTTPS'])) {
			$protocol = "https";
	} else if (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
		$protocol = strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']);
	}
	return $protocol;
}

function wppl_unparse_url($parsed_url) { 
	$scheme   = isset($parsed_url['scheme'])   ? $parsed_url['scheme'] . '://' : ''; 
	$host     = isset($parsed_url['host'])     ? $parsed_url['host']           : ''; 
	$port     = isset($parsed_url['port'])     ? ':' . $parsed_url['port']     : ''; 
	$user     = isset($parsed_url['user'])     ? $parsed_url['user']           : ''; 
	$pass     = isset($parsed_url['pass'])     ? ':' . $parsed_url['pass']     : ''; 
	$pass     = ($user || $pass)               ? "$pass@"                      : ''; 
	$path     = isset($parsed_url['path'])     ? $parsed_url['path']           : ''; 
	$query    = isset($parsed_url['query'])    ? '?' . $parsed_url['query']    : ''; 
	$fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : ''; 
	return "$scheme$user$pass$host$port$path$query$fragment"; 
}
