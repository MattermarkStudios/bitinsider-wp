<?php
function stm_set_layout_options($layout)
{
	global $wp_filesystem;

	if (empty($wp_filesystem)) {
		require_once ABSPATH . '/wp-admin/includes/file.php';
		WP_Filesystem();
	}

	$options = STM_CONFIGURATIONS_PATH . '/importer/demos/' . $layout . '/options/theme_mods.json';

	if (file_exists($options)) {
		$encode_options = $wp_filesystem->get_contents($options);
		$import_options = json_decode( $encode_options, true );

		$default = get_option('theme_mods_crypterio', array());

		foreach ( $import_options as $key => $value ) {
			update_option( $key, $value );

			$default[$key] = $value;
		}

		update_option('theme_mods_crypterio', $default);
	}


}