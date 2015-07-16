<?php
namespace WPX\Utils;

function getPagination(){
	$defaultPagination = ['offset'=> 0, 'limit'=> 10];

	if (isset($_GET['page'])) {
		$page = $_GET['page'];

		if (is_array($page)) 
			$page = $page + $defaultPagination;
	 else 
			$page = [
				'offset' => $defaultPagination['limit'] * $page
			] + $defaultPagination;
		
		foreach($page as &$attr) 
			$attr = intval($attr);
		
	} else 
		$page = [] + $defaultPagination;	

	return $page;
}


