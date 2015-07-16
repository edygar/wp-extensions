<?php
namespace WPExtensions\Utils;

function input($settings) {
  $name = value($settings,'name');	
  $label = value($settings,'label');	
  $value_field = value($settings,'value_field');	
  $label_field = value($settings,'label_field');	
  $multiple = value($settings,'multiple');
  $disabled = value($settings,'disabled');
  $items = value($settings,'items');
  $required = value($settings,'required');

  if ($multiple > 0) {
	  $type = 'checkbox';
  	$name.= "[]";
  }
	else 
		$type = 'radio';
	?>
	<div style="line-height: 2em">
		<?php echo $label?"$label:<br/>":""; ?>
		<?php if ($multiple === false && $required === false): ?>
			<label>
				<input
					name="<?php echo $name ?>"
					type="<?php echo $type ?>"
					value=""
					<?php disabled($disabled); ?>
					<?php checked(empty($items)) ?>
				/> <?php echo value($settings,'none_label', "None") ?>
			</label>
			<br/>
		<?php endif ?>
		<?php foreach ($settings['options'] as $option) : ?>
			<label>
				<input
					name="<?php echo $name ?>"
					type="<?php echo $type ?>"
					<?php disabled($disabled); ?>
					<?php checked(in_array($option->{$value_field}, $items)) ?>
					value="<?php
						echo apply_filters(
							"option_value_field",
							esc_attr( $option->{$value_field} ),
							$option,
							$settings
						);
					?>"
				/> <?php
					echo apply_filters(
						"option_label_field",
						esc_html( $option->{$label_field} ),
						$option,
						$settings
					);
				?>
			</label><br/>
		<?php endforeach; ?>
		</div>
	<?php
}

function select($settings) {
  $value_field = value($settings,'value_field');	
  $label_field = value($settings,'label_field');	
  $none_label = value($settings,'none_label', "None");
  $name = value($settings,'name');	
  $label = value($settings,'label');	
  $multiple = value($settings,'multiple');
  $disabled = value($settings,'disabled');
  $options = value($settings,'options');
  $items = value($settings,'items');
  $required = value($settings,'required');

	?>
	<label>
		<?php echo $label; ?>
		<select
			<?php disabled($disabled); ?>
			<?php echo $multiple > 0?'multiple':'' ?>
			name="<?php echo $name; ?>[]"
		>
			<?php if (!$required && !$multiple): ?>
				<option value="0"><?php echo  $none_label; ?></option>
			<?php endif ?>
			<?php foreach ($options as $option) : ?>
				<option
					<?php selected(in_array($option->{$value_field}, $items)) ?>
					value="<?php
						echo apply_filters(
							"option_value_field",
							esc_attr( $option->{$value_field} ),
							$options,
							$settings
						);
					?>"
				><?php
					echo apply_filters(
						"option_label_field",
						esc_html( $option->{$label_field} ),
						$options,
						$settings
					);
				?></option>
			<?php endforeach; ?>
		</select>
	</label>
	<?php
}
