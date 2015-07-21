<?php
namespace WPExtensions\Utils;

function console_log() {
	foreach((array)func_get_args() as $current) {
		?><script>console.log(<?php echo json_encode($current); ?>)</script><?php
	}
}

function console_error() {
	foreach((array)func_get_args() as $current) {
		?><script>console.error(<?php echo json_encode($current); ?>)</script><?php
	}
}

function console_warn() {
	foreach((array)func_get_args() as $current) {
		?><script>console.warn(<?php echo json_encode($current); ?>)</script><?php
	}
}

function console_table() {
	foreach((array)func_get_args() as $current) {
		?><script>console.table(<?php echo json_encode($current); ?>)</script><?php
	}
}
	
