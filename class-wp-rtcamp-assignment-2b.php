<?php
/**
 *  Assignment-2b: WordPress-Contributors Plugin
 *
 * @package rtCamp
 * @version 0.1
 */

/*
Plugin Name: Assignment-2b: WordPress-Contributors Plugin
Plugin URI:  http://tymescripts.com/rtCamp-assignment
Description: WordPress-Contributors Plugin, adds a contributor metabox box with names and their Gravatars to posts section. Selected contributors are shown in frontend for posts for which they have contributed.
Version:     0.1
Author:      Abhijeet Bendre
Author URI:  http://tymescripts.com
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Domain Path: /languages
Text Domain: wprtc_assignment_2b
*/

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

define( 'WPRTC_2B_PLUGIN_NAME', 'wp-rtcamp-assignment-2b' );
define( 'WPRTC_2B_PLUGIN_URL', trailingslashit( plugin_dir_url( __FILE__ ) ) );

/**
 * RtCamp Assignment 2b Class.
 *
 * @category Class
 *
 * @since 0.1
 */
class Wp_Rtcamp_Assignment_2b {

	/**
	 * Constructor for this class
	 *
	 * @since 0.1
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'wprtc_setup_contributors_metabox' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'wprtc_init_assets' ) );
	}


	/**
	 * Init assets such as JS/CSS, required by plugin
	 *
	 * @since 0.1
	 */
	public function wprtc_init_assets() {
		global $pagenow;

		/*
		 * Register and Enqueue Style/Scripts only on post_type 'post'.
		 *
		 * Check if $_GET['post_type'] exists. For "All Posts/ Add new Post" screen .
		 *	or
		 * Check if $_GET['post'] exists. (For Edit Post Screen).
		 */
		$post_type = isset( $_GET['post_type'] ) ? sanitize_text_field( wp_unslash( $_GET['post_type'] ) ) : ''; // Input var okay. WPCS: CSRF ok.
		$post_id = isset( $_GET['post'] ) ? sanitize_text_field( wp_unslash( $_GET['post'] ) ) : ''; // Input var okay. WPCS: CSRF ok.

		if ( ( 'post' === $post_type && in_array( $pagenow, array( 'post-new.php', 'edit.php' ), true ) )
				||
				( 'post.php' === $pagenow && 'post' === get_post_type( $post_id ) )
			) {
			// Register and Enqueue Style.
			wp_register_style( 'wprtc_contributors_main_2b_css', plugin_dir_url( __FILE__ ) . 'assets/css/wprtc_contributors_main_2b.css', null );
			wp_enqueue_style( 'wprtc_contributors_main_2b_css' );
		}
	}

	/**
	 * Setup Metaboxes for contributors
	 *
	 * @since 0.1
	 */
	public function wprtc_setup_contributors_metabox() {
		add_meta_box( 'wprtc_contributors',  __( 'Contributors', 'wprtc_assignment_2b' ), array( $this, 'wprtc_render_contributors_metabox' ), 'post', 'normal', 'low' );
	}

	/**
	 * Render contributors metabox.
	 *
	 * @since 0.1
	 */
	public function wprtc_render_contributors_metabox() {
		ob_start();
		$all_users = get_users();
		//var_dump($all_users);
		if ( ! empty( $all_users ) ) {
			echo "<table class='wprtc_contributors_table' cellspacing='0'>
							<thead>
								<tr>
									<th class='wprtc_contributors_head' scope='col'></th>
					 				<th class='wprtc_contributors_head' scope='col'>Author Name</th>
					 				<th class='wprtc_contributors_head' scope='col'>Gravatar</th>
								</tr>
							</thead>
							<tbody>";
			foreach ( $all_users as $all_users_key => $all_users_value ) {
				echo "<tr>
        					<td class=''><input type='checkbox' name='_wprtc_contributor' value='" . esc_attr( $all_users_value->ID ) . "'></td>
        					<td class=''>$all_users_value->user_login</td>
									<td class=''>" . get_avatar( $all_users_value->ID, 32 ) . '</td>
        			</tr>';
			}
			echo '</tbody></table>';
			ob_get_flush();
		}
	}
}

new Wp_Rtcamp_Assignment_2b();
