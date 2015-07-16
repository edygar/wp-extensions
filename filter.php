<?php
namespace WPX\Filter;

function escoped_filters(Array $filters, callable $escoped) {
	foreach($filters as $filter => $function)
		add_filter($filter, $function);

	$result = call_user_func($escoped);

	foreach($filters as $filter => $function)
		remove_filter($filter, $function);
	

	return $result;
}