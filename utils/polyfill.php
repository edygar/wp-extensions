<?php
namespace WPExtensions\Utils;

function is_assoc($array){
  return (array_values($array) !== $array);
}


function array_is_numeric(&$array) {
	foreach ($array as $b) 
    if (!is_int($b)) return false;
    
	return true;
}