<?php
namespace WPExtensions\Query;

/**
 * Iterates query results until iterator callback returns falsy
 * $none_cb is invoked when query has no results
 */
function loop_query($query, $iterator = null, $none_cb = null)
{
  global $wp_query;

  if (!$query instanceof \WP_Query) {
    $none = $iterator;
    $iterator = $query;
    $query = $wp_query;
  }

	if ($query->have_posts()) {
		while($query->have_posts()) {
			$query->the_post();
			$break = call_user_func_array($iterator, [$query, get_post()]);
			if ($break === false) break;
		}
    $query->rewind_posts();
		wp_reset_query();
	}
	elseif ($none_cb) {
		call_user_func_array($none_cb, [$query]);
	}
}

/**
 * Returns all query results ids
 */
function get_query_IDs($query) {
  $ids = [];

  loop_query($query, function() use (&$ids) {
    $ids[] = get_the_ID();
  });

  return $ids;
}
