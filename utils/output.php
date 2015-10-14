<?php
namespace WPExtensions\Utils;

function capture_output($fn) {
	ob_start();
	call_user_func($fn);
	$output = ob_get_contents();
	ob_end_clean();
	return $output;
}