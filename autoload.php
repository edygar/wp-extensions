<?php
/*
	Plugin Name: WP Extensions
	Version: 0.1.0
	Description: Wordpress Extensions
	Author: Edygar de Lima Oliveira <edygardelima@gmail.com>
	Author URI: http://www.github.com/edygar
	Text Domain: concurso
	Depends: Advanced Custom Fields
*/

// helpers
include 'utils/polyfill.php';
include 'utils/console.php';
include 'utils/params.php';
include 'utils/api.php';
include 'utils/html.php';
include 'filter.php';
include 'search.php';
include 'cache.php';
include 'taxonomy-metabox.php';
include 'taxonomy-metabox/search-n-select.php';
