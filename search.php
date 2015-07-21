<?php
namespace WPExtensions\Search;
require_once('filter.php');

use function \WPExtensions\Filter\escoped_filters as escoped_filters;
use function WPExtensions\Utils\value as value;

function search_terms($taxonomy, Array $options) {
	$options += [
		'hide_empty' => false,
		'childless'	=> false,
		'pad_counts' => true,
		'level' => null
	];

	if ($raw_query = value($options,'search', null)) {
		$raw_query = trim($raw_query);
		$query = preg_replace('@\s+@smi','%', trim($raw_query));
		unset($options['search']);
	}
	else
		$query = null;

	if (value($options, 'level', null)===0) {
		unset($options['level']);
		$options['parent'] = 0;
	}

	return escoped_filters([
		'terms_clauses' => function($clauses) use ($raw_query, $query, $options, $taxonomy) {
			global $wpdb;

			if (($level_and_above = value($options,'level_and_above', null)) !== null) {
				$options['level'] = $level_and_above;
			}

			if (value($options,'level', null)) {
				$join = ($level_and_above?" LEFT JOIN":" INNER JOIN");

				$clauses["join"] .= " $join $wpdb->term_taxonomy AS ttl0 ON ttl0.taxonomy = '$taxonomy' and ttl0.term_taxonomy_id = tt.parent ";

				for($i=1; $i  < $options['level']; $i++) {
					$clauses["join"] .= "$join $wpdb->term_taxonomy AS ttl$i ON ttl$i.taxonomy = '$taxonomy' and ttl$i.term_taxonomy_id = ttl".($i-1).".parent ";
					
					if ($level_and_above) 
						$roots[] = "ttl$i.parent";
				}

				if ($level_and_above) {
					$roots[] = "ttl0.parent";
					$roots[] = "ttl".($i-1).".parent";
					$roots[] = "tt.parent";
				}
				else
					$roots = ["ttl".($options['level']-1).".parent"];

				$clauses["where"] .= " AND 0 IN (".implode(",",$roots).")";
			}

			if ($query) {
				$clauses = [
					'fields' => "$clauses[fields], ".$wpdb->prepare(
							'CASE
									WHEN t.name = %s THEN %d
									WHEN t.name like %s THEN %d
									WHEN t.name like %s THEN %d
									WHEN t.name like %s THEN %d
								ELSE
									0
								END as score
							',
							[
								$raw_query, 10,
								$raw_query, 8,
								"$raw_query%", 6,
								"$query%", 4,
							]
						),
					'where' => $clauses['where'].$wpdb->prepare('
						 AND (
								(t.name LIKE %s) OR (t.slug LIKE %s)
							) ',
							["%$query%", "%$query%"]
						),
					'orderby' => 'ORDER BY score DESC, length(t.name) ASC, t.name ',
				] + $clauses;
			}

			return $clauses;
		},
	],
	function() use ($options, $taxonomy) {
		return get_terms($taxonomy, $options);
	});	
}

function terms_eager_loading($terms) {
	foreach((Array)$terms as $term) {
		if ($term->parent) {
			$parent = get_term_by('id', $term->parent, $term->taxonomy);
			$term->parent	= current(terms_eager_loading([$parent]));
			$term->parents = $term->parent->parents + 1;
		}
		else
			$term->parents = 0;
	}

	return $terms;
}