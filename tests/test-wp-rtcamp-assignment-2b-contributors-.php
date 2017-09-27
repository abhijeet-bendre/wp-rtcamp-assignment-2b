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
	 * CPT slug for slide show.
	 *
	 * @var static protected $whitelist_user_roles
	 */
	protected static $whitelist_user_roles = array();

	/**
	 * Test if Plugin is active.
	 */
	function test_is_plugin_active() {
		$this->assertTrue( is_plugin_active( WPRTC_2B_CONTRIBUTORS_PLUGIN_NAME . '/' . WPRTC_2B_CONTRIBUTORS_PLUGIN_NAME . 'php' ) );
	}

	/**
	 * Setup of 'setUpBeforeClass' test fixture
	 */
	public static function setUpBeforeClass() {
		// Call parent's setUpBeforeClass method.
		parent::setUpBeforeClass();

		self::$whitelist_user_roles = array(
			'administrator' => self::factory()->user->create_and_get(
				array(
					'role' => 'administrator',
				)
			),
			'author' => self::factory()->user->create_and_get(
				array(
					'role' => 'author',
				)
			),
			'editor' => self::factory()->user->create_and_get(
				array(
					'role' => 'editor',
				)
			),
		);
	}

	/**
	 * Test if selected Post Contributors are saved.
	 */
	function test_if_selected_post_contributors_are_saved() {
		// Simulate $_POST variable for save_post hook.
		$_POST['post_type'] = 'post';

		// Simulate $_POST with fake post contributor id 2.
		$_POST['_wprtc_contributors'] = array( 2 );

		// 'admin_int' is not fired in test suite, so simulate it by 'wprtc_admin_init'.
		do_action( 'wprtc_admin_init' );

		// Create a Single Post.
		foreach ( self::$whitelist_user_roles as $whitelist_user_role => $whitelist_user_object ) {
			wp_set_current_user( $whitelist_user_object->ID );
				// Simulate $_POST variable for nonce.
			$contributors_nonce = wp_create_nonce( '_wprtc_contributor_metabox_nonce' );
			$_POST['_wprtc_contributor_metabox_nonce'] = $contributors_nonce;

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
}
