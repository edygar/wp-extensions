<?php
namespace WPExtensions\Utils;

function captureToString(callable $fn) {
	ob_start();
	call_user_func($fn);
	$result = ob_get_contents();
	ob_end_clean();
	return $result;
}