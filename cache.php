<?php
namespace WPX\Cache;

function cached_result($cache_key, $fn) {
	if (!is_preview() && $result = wp_cache_get($cache_key))
		return $result;

	$result = call_user_func($fn);
	
	if (!is_preview()) 
		wp_cache_set($cache_key, $result);

	return $result;
}

