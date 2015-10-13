<?php
namespace WPExtensions\Utils;

function value(&$source, $key, $defaultsTo = null) {
	try{
		if (is_object($source) && property_exists($source, $key))
			return $source->{$key};

		else if (is_array($source) && isset($source[$key])) 
			return $source[$key];

		return $defaultsTo;

	} catch (Exception $e) {
		// Shhh!
	}
}

function _GET($key, $defaultsTo=null) {
	return value($_GET, $key, $defaultsTo);
}

function _POST($key, $defaultsTo=null) {
	return value($_POST, $key, $defaultsTo);
}