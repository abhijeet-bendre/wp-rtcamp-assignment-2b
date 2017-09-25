<?php
/**
 * Class Wp_Rtcamp_Assignment_2b_Contributors_Test
 *
 * @package Wp_Rtcamp_Assignment_2b
 */

/**
 * Test case for rtCamp assignment 2b WordPress-Contributors Plugin
 */
class Wp_Rtcamp_Assignment_2b_Contributors_Test extends WP_UnitTestCase {

	/**
	 * Test if Plugin is active.
	 */
	function test_is_plugin_active() {
		$this->assertTrue( is_plugin_active( WPRTC_2B_CONTRIBUTORS_PLUGIN_NAME . '/' . WPRTC_2B_CONTRIBUTORS_PLUGIN_NAME . 'php' ) );
	}

	/**
	 * Test if selected Post Contributors are saved.
	 */
	function test_if_selected_post_contributors_are_saved() {
		// Simulate $_POST variable for save_post hook.
		$_POST['post_type'] = 'post';

		// Simulate $_POST variable for nonce.
		$contributors_nonce = wp_create_nonce( '_wprtc_contributor_metabox_nonce' );
		$_POST['_wprtc_contributor_metabox_nonce'] = $contributors_nonce;

		// Simulate $_POST with fake post contributor id 2.
		$_POST['_wprtc_contributors'] = array( 2 );

		// Create a Single Post.
		$post_id  = $this->factory()->post->create(
			array(
				'post_status' => 'publish',
				'post_title' => 'Post Title ',
			)
		);

		$wprtc_post_contributors = get_post_meta( $post_id, '_wprtc_contributors' );

		$wprtc_post_contributors = $wprtc_post_contributors[0];
		$this->assertEquals( 2, $wprtc_post_contributors[0] );
	}
}
