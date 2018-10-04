<?php
	/*
	Plugin Name: STM Configurations
	Plugin URI: https://stylemixthemes.com/
	Description: STM Configurations
	Author: Stylemix Themes
	Author URI: https://stylemixthemes.com/
	Text Domain: stm-configurations
	Version: 2.0
	*/

	$is_stm_theme = !empty(get_option('stm_theme_version'));


	define('STM_CONFIGURATIONS_PATH', dirname(__FILE__));
	define('STM_CONFIGURATIONS_URL', plugin_dir_url(__FILE__));

	if (!is_textdomain_loaded('stm-configurations')) {
		load_plugin_textdomain('stm-configurations', false, 'stm-configurations/languages');
	}

	/*Custom icons*/
	require_once STM_CONFIGURATIONS_PATH . '/iconloader/stm-custom-icons.php';

	/*Post type*/
	require_once STM_CONFIGURATIONS_PATH . '/post-type/stm-post-type.php';

	/*Events*/
	require_once STM_CONFIGURATIONS_PATH . '/events/events.php';

	/*Helpers*/
	require_once STM_CONFIGURATIONS_PATH . '/helpers/helpers.php';

	/*Megamenu*/
	require_once STM_CONFIGURATIONS_PATH . '/megamenu/main.php';

	/*Megamenu*/
	require_once STM_CONFIGURATIONS_PATH . '/ico_directory/index.php';

	if (is_admin()) {

		/*Demo import*/
		require_once STM_CONFIGURATIONS_PATH . '/importer/importer.php';
	}