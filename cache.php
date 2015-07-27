<?php
namespace WPExtensions\Cache;

define('CACHE_NAMESPACES', 'cache_namespaces');

function cached_result($cache_key, $cache_group, $fn = null) {
	if (is_null( $fn )) {
		$fn = $cache_group;
		$cache_group = '';
		$namespaced = false;
	}

	if ($cache_group) {
		$namespaced = wp_cache_get( $cache_group, 'cache_namespaces' );
		if ($namespaced === false) {
			wp_cache_set( $cache_group, 1, CACHE_NAMESPACES );
		}
	}

	if (!is_preview() && $namespaced && $result = wp_cache_get($cache_key, $cache_group))
		return $result;

	$result = call_user_func($fn);
	
	if (!is_preview()) {
		wp_cache_set($cache_key, $result, $cache_group);

		if ($cache_group) 
			wp_cache_incr($cache_group, CACHE_NAMESPACES);
		
	}

	return $result;
}


function flush_cache_group_on($cache_group, $actions = []) {
	flush_cache_on($cache_group, CACHE_NAMESPACES, $actions);
}

function flush_cache_on($cache_key, $cache_group, $actions = null) {
	if (is_null( $actions )) {
		$actions = $cache_group;
		$cache_group = '';
	}

	foreach ((array)$actions as $action) {
		add_action($action, function() use ($cache_key, $cache_group){
			wp_cache_delete($cache_key, $cache_group);
		});
	}	
}