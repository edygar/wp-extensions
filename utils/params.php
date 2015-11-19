<?php
namespace WPExtensions\Utils;

function value(&$source, $keys, $defaultsTo = null) {
	try{
    if (!is_array($keys)) $keys = [$keys];

    $value = $source;
    foreach($keys as $key) {
      if (is_object($value) && property_exists($value, $key))
        $value =  $value->{$key};

      else if (is_array($value) && isset($value[$key]))
        $value = $value[$key];

      else
        return $defaultsTo;
    }
    return $value;
	} catch (Exception $e) {
		// Shhh!
	}
}

function thruthy_value(&$source, $keys, $defaultsTo = null) {
  $value = value($source, $keys, $defaultsTo);
  return $value? $value: $defaultsTo;
}


function _GET($key, $defaultsTo=null) {
	return value($_GET, $key, $defaultsTo);
}

function _POST($key, $defaultsTo=null) {
	return value($_POST, $key, $defaultsTo);
}
