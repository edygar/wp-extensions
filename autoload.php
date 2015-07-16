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
require_once 'utils/polyfill.php';
require_once 'utils/console.php';
require_once 'utils/params.php';
require_once 'utils/api.php';
require_once 'utils/html.php';
require_once 'filter.php';
require_once 'search.php';
require_once 'cache.php';
require_once 'taxonomy-metabox.php';
require_once 'taxonomy-metabox/search-n-select.php';
