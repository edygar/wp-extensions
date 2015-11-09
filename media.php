<?php
namespace WPExtensions\Media;

function get_attachment_image_data($attachment_id, $size = 'thumbnail', $icon=false) {
  $image = wp_get_attachment_image_src($attachment_id, $size, $icon);

  if ( $image ) {
    $attachment = get_post($attachment_id);
    $image += [
      'alt'   => trim(strip_tags( get_post_meta($attachment_id, '_wp_attachment_image_alt', true) )), // Use Alt field first
    ];

    if ( empty($image['alt']) )
      $image['alt'] = trim(strip_tags( $attachment->post_excerpt )); // If not, Use the Caption
    if ( empty($default_attr['alt']) )
      $image['alt'] = trim(strip_tags( $attachment->post_title )); // Finally, use the title
  }

  return $image;
}
