<?php
/**
 * PHPUnit bootstrap file
 *
 * @package Wp_Rtcamp_Assignment_2b
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
	require dirname( dirname( __FILE__ ) ) . '/class-wp-rtcamp-assignment-2b.php';

	// Update array with plugins to include ...
	$plugins_to_active = array(
		WPRTC_2B_CONTRIBUTORS_PLUGIN_NAME . '/' . WPRTC_2B_CONTRIBUTORS_PLUGIN_NAME . 'php',
	);

	update_option( 'active_plugins', $plugins_to_active );
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';
