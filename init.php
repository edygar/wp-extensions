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
require_once 'utils/html.php';
require_once 'filter.php';
require_once 'search.php';
require_once 'cache.php';
require_once 'taxonomy-metabox.php';
require_once 'taxonomy-metabox/search-n-select.php';

/**
 * Searches a given query and taxonomy, serves the found terms in JSON format
 */
add_action( 'wp_ajax_tsns_search', function() {
	$request = [
		'taxonomy' => _GET('taxonomy'),
		'query' => _GET('query')
	] + getPagination();

	extract($request);

	// When looking for specifically 
	if ($post_id = _GET('post_id')) 
		$terms = wp_get_object_terms($post_id, $taxonomy);
	else 
		// When searching through query
		$terms = Search\search_terms($taxonomy,
			apply_filters('tsns_search_params', apply_filters('tsns_search_terms_params', [
			'search' => $query,

			// pagination
			'offset' => $offset,
			'number' => $limit ])));
	
	header('Content-Type: application/json');
	wp_die(json_encode(
		apply_filters(
			'tsns_search_response',
			$terms )));
});


/**
 * Takes $_POST['tax_input_tsns'] and converts it to 'tax_input' (Wordpress's
 * default mechanism)
 */
add_action( 'wp_loaded' , function() {
	if (!isset($_POST['tax_input_tsns']))
		return;

	$tax_input_tsns = $_POST['tax_input_tsns'];
	$tax_input = (Array)_POST('tax_input', []);

	foreach ( $tax_input_tsns as $taxonomy => $terms ) {
		/*
		 * Assume that a 'tax_input' string is a comma-separated list of term names.
		 * Some languages may use a character other than a comma as a delimiter, so
		 * we standardize on commas before parsing the list.
		 */
		if ( ! is_array( $terms ) ) {
			$comma = _x( ',', 'tag delimiter' );
			if ( ',' !== $comma ) {
				$terms = str_replace( $comma, ',', $terms );
			}
			$terms = explode( ',', trim( $terms, " \n\t\r\0\x0B," ) );
		}


		$clean_terms = array();
		foreach ( $terms as $term ) {
			// Empty terms are invalid input.
			if ( empty( $term ) ) {
				continue;
			}
			$_term = get_terms( $taxonomy, array(
				'name' => $term,
				'fields' => 'ids',
				'hide_empty' => false,
			) );


			if ( ! empty( $_term ) ) {
				$clean_terms[] = intval( $_term[0] );
			} else {
				// No existing term was found, so pass the string. A new term will be created.
				$clean_terms[] = $term;
			}
		}
		$tax_input[ $taxonomy ] = $clean_terms;
	}

	$_POST["tax_input"] = $tax_input;
});

// defines styling
add_action('admin_enqueue_scripts', function() {
	wp_enqueue_script( 'sifter',
		plugins_url( 'wpx/vendor/sifter/sifter.min.js' ),
		[], '0.4.x', false );

	wp_enqueue_script( 'microplugin',
		plugins_url( 'wpx/vendor/microplugin/src/microplugin.js' ),
		[], '0.0.x', false );

	wp_enqueue_script( 'selectize-tsn',
		plugins_url( 'wpx/vendor/selectize/dist/js/selectize.min.js' ),
		['jquery', 'underscore', 'microplugin', 'sifter'], '0.1.0', false );

	wp_enqueue_style( 'selectize-tsn',
		plugins_url( 'wpx/vendor/selectize/dist/css/selectize.bootstrap2.css'));
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