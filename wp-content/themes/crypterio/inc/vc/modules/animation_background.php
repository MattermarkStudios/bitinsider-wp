<?php

add_action('init', 'crypterio_moduleVC_animation_background');

function crypterio_moduleVC_animation_background()
{
    vc_map(array(
        'name'        => esc_html__('Animation background', 'crypterio'),
        'base'        => 'animation_background',
        'icon'        => 'animation_background',
        'category'    => array(
            esc_html__('Content', 'crypterio'),
        ),
        'params'   => array(
            array(
                'type'       => 'attach_image',
                'heading'    => esc_html__('Svg image', 'crypterio'),
                'param_name' => 'svg',
            ),
        )
    ));
}

if (class_exists('WPBakeryShortCode')) {
    class WPBakeryShortCode_Animation_Background extends WPBakeryShortCode
    {
    }
}