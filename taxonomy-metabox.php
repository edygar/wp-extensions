<?php

namespace WPX\Taxonomy;

use function WPX\Utils\is_assoc as is_assoc;
use function WPX\Utils\value as value;

class TaxonomyMetabox {
	const TYPES = [
		'select' => 'WPX\Utils\select',
		'input' => 'WPX\Utils\input'
	];
	
	static $defaultSettings = [
		"value_field" => "term_id",
		"label_field" => "name",
		'label' => false,
		'multiple' => false,
		'required' => false,
		'preload' => 'all',
		'renderer' => 'WPX\Utils\input'
	];

	protected $settings = [];

	public function __construct($settings = []) {
		$this->settings = $settings + self::$defaultSettings;
	}	

	public function get_options(\WP_Post $post, Array $box, \stdClass $taxonomy) {
    $preload = value($this->settings,'preload'); 
    $get_terms_args = [ 'hide_empty' => false ];

    if (is_array($preload)) {
    	if (is_assoc($preload)) 
    		$get_terms_args = $preload + $get_terms_args;
    		
    	elseif (array_is_numeric($preload))	 
  			$get_terms_args['include']	= $preload;

  		else return $preload;
    }
    elseif (is_integer($preload))
  		$get_terms_args['number'] = $preload;

  	elseif (!$preload)
  		return [];

  	$get_terms_args = apply_filters(
  		'tf_preload_get_terms_args',
  		$get_terms_args, $this->settings, $post, $box, $taxonomy
  	);

  	$get_terms_args = apply_filters(
  		sprintf('tf_%s_preload_get_terms_args', $taxonomy->name),
  		$get_terms_args, $this->settings, $post, $box, $taxonomy
  	);

  	return get_terms($taxonomy->name, $get_terms_args);
	}

	public function get_items(\WP_Post $post, Array $box, \stdClass $taxonomy)
	{
  	$wp_get_object_terms_args = apply_filters(
  		'tf_items_wp_get_object_terms_args',
  		['fields'=>'ids'], $this->settings, $post, $box, $taxonomy
  	);
    return wp_get_object_terms($post->ID, $taxonomy->name, apply_filters(
  		sprintf('tf_%s_items_wp_get_object_terms_args', $taxonomy->name),
  		$wp_get_object_terms_args, $this->settings, $post, $box, $taxonomy
  	));
	}

	public function setupRender(\WP_Post $post, Array $box, \stdClass $taxonomy) {
    $labels = get_taxonomy_labels($taxonomy);

		$renderSettings = [
		  'name' => 'tax_input['. esc_attr( $taxonomy->name ).']',
			'delimiter' => _x( ',', 'tag delimiter' ),
			'options'	=> $this->get_options($post, $box, $taxonomy),
			'items'	=> $this->get_items($post, $box, $taxonomy),
			'disabled' => !current_user_can($taxonomy->cap->assign_terms),
		] + $this->settings;

		if (value($renderSettings, 'label') === true) {
			$renderSettings['label'] = $labels->{
				value($renderSettings,'multiple',false)?
				"singular_name":
				"name"
			};
		}

		$renderSettings = apply_filters( 'tf_setup_render',
			$renderSettings, $post, $box, $taxonomy
		);

		return apply_filters(
  		sprintf('tf_%s_setup_render', $taxonomy->name),
			$renderSettings, $post, $box, $taxonomy
		);
	}

	public function __invoke(\WP_Post $post, Array $box)
	{
		$taxonomy = get_taxonomy($box['args']['taxonomy']);
		$renderSettings = $this->setupRender($post, $box, $taxonomy);

		call_user_func($renderSettings['renderer'], $renderSettings);
	}
}

