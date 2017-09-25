<?php
/**
 * Class Wp_Rtcamp_Assignment_2b_Contributors_Test
 *
 * @package Wp_Rtcamp_Assignment_2b
 */

/**
 * Sample test case.
 */
class Wp_Rtcamp_Assignment_2b_Contributors_Test extends WP_UnitTestCase {

	/**
	 * Test if Plugin is active.
	 */
	function test_is_plugin_active() {
		$this->assertTrue( is_plugin_active( WPRTC_2B_CONTRIBUTORS_PLUGIN_NAME . '/' . WPRTC_2B_CONTRIBUTORS_PLUGIN_NAME . 'php' ) );
	}

}
