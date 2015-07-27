<?php
namespace WPExtensions\Action;
require_once('filter.php');

use function \WPExtensions\Filter\escoped_filters;
use function \WPExtensions\Search\search_terms;
use function \WPExtensions\Utils\value;
use function \WPExtensions\Utils\_GET;
use function \WPExtensions\Utils\_POST;
use function \WPExtensions\Utils\getPagination;

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
	else if ($term_id = _GET('term_id')) 
		$terms = [ get_term($term_id, $taxonomy) ];
	else 
		// When searching through query
		$terms = search_terms($taxonomy,
			apply_filters('tsns_search_params', apply_filters('tsns_search_terms_params', [
			'search' => $query,

			// pagination
			'offset' => $offset,
			'number' => $limit ])));

	// header('Content-Type: application/json');
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
			if ( empty( $term ) ) 
				continue;

			if (!is_numeric($term) or ( $_term = get_term_by('id', $term, $taxonomy)) === false)
				$_term = get_terms( $taxonomy, array(
					'name' => $term,
					'fields' => 'ids',
					'hide_empty' => false,
				) );
			else
				$_term = [$_term->term_id];

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
