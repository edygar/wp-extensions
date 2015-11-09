<?php
/*
	Plugin Name: WP Extensions
	Version: 0.1.0
	Description: Wordpress Extensions
	Author: Edygar de Lima Oliveira <edygardelima@gmail.com>
	Author URI: http://www.github.com/edygar
*/

namespace WPExtensions;

// helpers
require_once 'utils/polyfill.php';
require_once 'utils/console.php';
require_once 'utils/params.php';
require_once 'utils/api.php';
require_once 'utils/capture.php';
require_once 'utils/html.php';
require_once 'filter.php';
require_once 'search.php';
require_once 'query.php';
require_once 'cache.php';
require_once 'taxonomy-metabox.php';
require_once 'taxonomy-metabox/search-n-select.php';

require_once 'actions.php';

// defines styling
add_action('admin_enqueue_scripts', function() {
	wp_enqueue_script( 'selectize-tsn',
		'https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.1/js/standalone/selectize.min.js',
		['jquery', 'underscore'], '1.12.1', false );

	wp_enqueue_style( 'selectize-tsn',
		 'https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.1/css/selectize.bootstrap2.min.css');
});

// defines the loading indicator style using WP's one
add_action('admin_head', function () {
	?>
	<style>
	.selectize-control.tsns::before {
		  -moz-transition: opacity 0.2s;
		  -webkit-transition: opacity 0.2s;
		  transition: opacity 0.2s;
		  content: ' ';
		  z-index: 2;
		  position: absolute;
		  display: block;
		  top: 50%;
		  right: 10px;
		  width: 16px;
		  height: 16px;
		  margin: -8px 0 0 0;
		  background: url(<?php echo admin_url("images/loading.gif"); ?>);
		  background-size: 16px 16px;
		  opacity: 0;
		}
	.selectize-control.tsns.single::before {
		right: 35px;
	}
	.selectize-control.tsns.loading::before {
		opacity: 1;
	}
	</style>
	<?php
});
