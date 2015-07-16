<?php
namespace WPX\Utils;

function value(&$array, $key, $defaultsTo = null) {
	try{
		return !isset($array[$key])? $defaultsTo: $array[$key];
	} catch (Exception $e) {
		var_dump($e);
	}
}

function _GET($key, $defaultsTo=null) {
	return value($_GET, $key, $defaultsTo);
}

function _POST($key, $defaultsTo=null) {
	return value($_POST, $key, $defaultsTo);
}