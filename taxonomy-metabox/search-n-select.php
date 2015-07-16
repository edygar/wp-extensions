<?php
namespace WPExtensions\TaxonomyMetabox;

use function WPExtensions\Utils\value as value;
use function WPExtensions\Utils\_GET as _GET;
use function WPExtensions\Utils\_POST as _POST;
use function WPExtensions\Utils\getPagination as getPagination;

/**
 * Generates a taxonomy metabox function callback together with seletize
 * @since 0.1.0
 *
 * @global array 	$defaultSettings	Default settings applied to all TSNS fields
 * @global wpdb 	$wpdb							WordPress database abstraction object.
 *
 * @param array $settings {
 * 		Settings to describe how the TSNS fields should work 
 *
 *		@type string		$ajax_url					url ajax target. Defaults to `ajaxurl`
 *		@type array 		$selector_setup		Selectize Setup 
 *		@type array 		$ajax_data				Aditional data to be served by ajax.
 *																		 	By default, wp_ajax settings are assumed.
 *		@type string		$ajax_query_field	field in ajax data for the query, which
 *																			is overwrited in runtime by current one.
 *
 *		@type	int|bool	$terms_limit		defines how much terms can be assigned to
 *																		the post. Define *false* for no limit.
 *																	
 * } *
 * @return function Returns a function which should be passed to 'meta_box_cb'
 * 									of {@link register_taxonomy()}'s $args
 *              		if no taxonomy is specified and the term ID exists. Returns
 *              		an array of the term ID and the term taxonomy ID the taxonomy
 *              		is specified and the pairing exists.
 */
function searchNSelectField($settings = []) {
	global $defaultSettings, $tsns_initiated;

	$settings += [
		'selector_setup'=> [ /* processed at presentation time */ ],
		'ajax_url' => false,
		'ajax_query_field' => 'query',
		'terms_limit' => false
	];

	return function(\WP_Post $post, Array $box) use ($settings)
	{
		$tax_name = $box['args']['taxonomy'];
		$taxonomy = get_taxonomy($tax_name);
    $user_can_assign_terms = current_user_can( $taxonomy->cap->assign_terms );
    $comma = _x( ',', 'tag delimiter' );
    $terms = wp_get_object_terms($post->ID, $tax_name); ;

    $labels = get_taxonomy_labels($taxonomy);
    $new_label = $labels->add_new_item;
    $preload = []; 

    if ($preload_length = value($settings,'preload', 10)) 
    	$preload = get_terms($tax_name, ['number' => $preload_length]);
    

    /**
     * Filters the selectize settings before its conversion to JSON
     *
     * @since 0.1.0
     *
     * @param array 	$settings	Original settings to passed to selectize.js
     * @param WP_Post $post 		The post currently being edited
     * @param object 	$taxonomy	Taxonomy for the current metabox
     * @param array 	$settings	Options this current fields
     */
    $config = apply_filters('tsns_selector_setup', $settings['selector_setup']+[
	    'valueField' => 'term_id',
	    'labelField' => 'name',
	    'searchField' => 'name',
	    'maxOptions' => 10,
	   	'optionsTemplate' => '<div class="option"><%- option.name %></div>',
	   	'createTemplate' => "<div class='option'>$new_label: <%- input %></div>",
	   	'itemsTemplate' => null,
	   	'maxItems' => $settings['terms_limit'],
    	'delimiter'=> $comma,
    	'options' => $terms + $preload,
    	'items' => array_map(function($term) { return $term->term_id; }, $terms),
    	'create' => $user_can_assign_terms,
    ], $post, $taxonomy, $settings );

    /**
     * Filters the selectize settings before its conversion to JSON specifically
     * to {taxonomy}, after filtered by {@link tsns_selector_setup}
     *
     * @since 0.1.0
     *
     * @param array 	$settings	Original settings to passed to selectize.js
     * @param WP_Post $post 		The post currently being edited
     * @param object 	$taxonomy	Taxonomy for the current metabox
     * @param array 	$settings	Options this current fields
     */
    $config = apply_filters(
    	sprintf('tsns_%s_selector_setup', $tax_name),
    	$config, $post, $taxonomy, $settings
    );

    /**
     * Filter ajax data to be sent along with the query 
     *
     * @since 0.1.0
     *
     * @param array 	$ajax_data	Original ajax data
     * @param WP_Post $post 			The post currently being edited
     * @param object 	$taxonomy		Taxonomy for the current metabox
     * @param array 	$settings		Options this current fields
     */
    $ajax_data = apply_filters('tsns_ajax_data', value($settings, 'ajax_data', [
	    	'action' => "tsns_search",
	    	'taxonomy' => $tax_name
	  	]), $post, $taxonomy, $settings );
    /**
     * Filter ajax data to be sent along with the query specifically to
     * {taxonomy}, after filtered by {@link tsns_setup_selector}
     *
     * @since 0.1.0
     *
     * @param array 	$settings	Original settings to passed to selectize.js
     * @param WP_Post $post 		The post currently being edited
     * @param object 	$taxonomy	Taxonomy for the current metabox
     * @param array 	$settings	Options this current fields
     */
    $ajax_data = apply_filters(
    	sprintf('tsns_%s_ajax_data', $tax_name),
    	$ajax_data, $post, $taxonomy, $settings
    );

  	if ($ajax_url = value($settings, 'ajax_url', false))
	  	$ajax_url = json_encode($ajax_url);
	  else
	  	$ajax_url = 'ajaxurl';

		?>
    <div class="selectize-taxonomy">
			<textarea
				name="<?php echo "tax_input_tsns[$tax_name]"; ?>"
				rows="3"
				cols="20"
				class="tsns"
				id="tax-input-<?php echo $tax_name; ?>"
				<?php disabled( ! $user_can_assign_terms ); ?>
			><?php
				echo str_replace( ',', $comma . ' ', get_terms_to_edit( $post->ID, $tax_name ) );
			?></textarea>
			<script type="text/javascript">
				(function($, _) {
					var $el = $("script:last").prev();
					<?php if (value($settings,'required')): ?>
					$el.closest("form").submit(function(e){
						if (!$el.val())  {
							e.preventDefault();
							e.stopPropagation();
							return false;
						}
					});
					<?php endif; ?>

					// Settings
					var options = <?php echo json_encode($config); ?>;
					var ajaxData = <?php echo json_encode($ajax_data) ?>;

					if (!options.maxItems)
						delete options.maxItems;

					// Templates
					var optionsTemplate = (options.optionsTemplate && _.template(options.optionsTemplate)) || null;
					var itemsTemplate = (options.itemsTemplate && _.template(options.itemsTemplate)) || null;
					var createTemplate = (options.createTemplate && _.template(options.createTemplate)) || null;

					// Methods
					options = $.extend({

						load: function(query, done) { 
							ajaxData['query'] = query;

							$.ajax({
								url: <?php echo $ajax_url ?>,
								type: 'GET',
								dataType: 'json',
								data: ajaxData,
								error: function() { done(); },
								success: function(res) { done(res); }
							});
						},

						render: {
							item: (itemsTemplate && function(item, escape) {
								return itemsTemplate({item:item, escape:escape});
							}) || undefined,
							option: (optionsTemplate && function(option, escape) {
								return optionsTemplate({option: option, escape:escape});
							}) || undefined,
							option_create: (createTemplate && function(query, serialize) {
								query.serialize = serialize;
								return createTemplate(query);
							}) || undefined
						}
					}, options);

					$el.selectize(options);
				})(jQuery, _);
			</script>
		</div>
		<?php
	};
}

