<?php
//include the WPML installer in the theme
WP_Installer_Setup($wp_installer_instance,
    array(
        'plugins_install_tab' => 1, // optional, default value: 0
        'affiliate_id:wpml' => '63216', // optional, default value: empty
        'affiliate_key:wpml' => '9NU1olUt3hlX', // optional, default value: empty
        'src_name' => 'Crypterio', // optional, default value: empty, needed for coupons
        'src_author' => 'StylemixThemes',// optional, default value: empty, needed for coupons
        'repositories_include' => array('wpml') // optional, default to empty (show all)
    )
);
