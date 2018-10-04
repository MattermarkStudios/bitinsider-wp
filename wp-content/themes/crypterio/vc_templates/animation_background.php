<?php
$atts = vc_map_get_attributes($this->getShortcode(), $atts);
extract( $atts );

wp_enqueue_style('animation_background', get_template_directory_uri() . '/assets/css/shared/vc/animation_background.css', array(), CRYPTERIO_THEME_VERSION);
if(!empty($svg)){
    $svg = file_get_contents(get_attached_file($svg), true);
}
else {
    $svg = file_get_contents(get_template_directory() . '/partials/animation_background/default_animation.svg', true);
}
?>

<div class="svg-animation-wrap">
    <?php echo html_entity_decode($svg); ?>
</div>