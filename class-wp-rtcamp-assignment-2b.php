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
		add_action( 'wp_enqueue_scripts', array( $this, 'wprtc_init_front_end_assets' ) );
		// 'save_post' callback for saving selected_contributors.
		add_action( 'save_post', array( $this, 'wprtc_save_selected_contributors' ), 10 );
		add_filter( 'the_content', array( $this, 'wprtc_append_contributors' ), 20 );
	}

	/**
	 * Enqueue assets such as JS/CSS, required by plugin
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
	 * Enqueue Front-end assets such as JS/CSS, required by plugin
	 *
	 * @since 0.1
	 */
	public function wprtc_init_front_end_assets() {
		// Register and Enqueue Style.
		wp_register_style( 'wprtc_contributors_front_end_2b_css', plugin_dir_url( __FILE__ ) . 'assets/css/wprtc_contributors_front_end_2b.css', null );
		wp_enqueue_style( 'wprtc_contributors_front_end_2b_css' );
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
		global $post;
		$all_users = get_users();
		$contributors_nonce = wp_create_nonce( '_wprtc_contributors_nonce' );
		//var_dump($all_users);
		$post_contributors = get_post_meta( $post->ID, '_wprtc_contributors' );
		if ( ! empty( $post_contributors ) ) {
			$post_contributors = $post_contributors[0];
		}

		if ( ! empty( $all_users ) ) {
			ob_start();
			echo "<table class='wprtc_contributors_table' cellspacing='0'>
							<thead class='wprtc_contributors_thead'>
								<tr>
									<th scope='col'></th>
					 				<th scope='col'>Author Name</th>
					 				<th scope='col'>Gravatar</th>
								</tr>
							</thead>
							<tbody class='wprtc_contributors_tbody'>";
			foreach ( $all_users as $single_user ) {
				echo "<tr>
        					<td class=''>
										<input type='checkbox' name='_wprtc_contributors[]' value='" . esc_attr( $single_user->ID ) . "' " . checked( in_array( $single_user->ID, $post_contributors ), true, false ) . ">
									</td>
        					<td class=''>$single_user->user_login</td>
									<td class=''>" . get_avatar( $single_user->ID, 75 ) . '</td>
        			</tr>';
			}
			echo "</tbody>
				<input type='hidden' name='_wprtc_contributor_metabox_nonce' value='" . esc_attr( $contributors_nonce ) . "'/>
			</table>";
			ob_get_flush();
		}
	}

	/**
	 * 'save_post' callback for saving selected Contributors.
	 *
	 * @param int $post_id Post Id.
	 *
	 * @since 0.1
	 */
	public function wprtc_save_selected_contributors( $post_id ) {
		global $post;
		$wprtc_post_contributors = array();

		/*
		* Check if valid post_type.
		*/
		if ( isset( $_POST['post_type'] ) ) { // Input var okay.
			if ( 'post' !== sanitize_text_field( wp_unslash( $_POST['post_type'] ) ) ) { // Input var okay.
				return;
			}
		}
		foreach ( $_POST as $post_key => $post_value ) { // Input var okay.
			// $key is input hidden, $value is attachment id.
			if ( strpos( $post_key, '_wprtc_contributors' ) !== false ) {
				$wprtc_post_contributors = $post_value;
				// Build Slides array to save in to post meta.
				array_walk( $wprtc_post_contributors, function( &$wprtc_value, &$wprtc_key ) {
						$wprtc_post_contributors[ $wprtc_key ] = $wprtc_value;
				});
			}
		}
		// Update contributors list.
		update_post_meta( $post_id, '_wprtc_contributors', $wprtc_post_contributors );
	}

	/**
	 * Append_contributors box to post.
	 *
	 * @param int $content Post Id.
	 *
	 * @since 0.1
	 */
	public function wprtc_append_contributors( $content ) {
		global $post;
		$post_contributors = '';

		if ( is_single() ) {
			$content .= $this->wprtc_display_contributors_box( $post->post_author );
			$post_contributors = get_post_meta( $post->ID, '_wprtc_contributors' );

			if ( ! empty( $post_contributors ) ) {
				$post_contributors = $post_contributors[0];
				foreach ( $post_contributors as $contributor_id ) {
					$content .= $this->wprtc_display_contributors_box( $contributor_id );
				}
			}
			// Returns the content.
			return $content;
		}
	}

	/**
	 * Render Contributor info box.
	 *
	 * @param int $contributor Contains id of or post contributor.
	 *
	 * @since 0.1
	 */
	public function wprtc_display_contributors_box( $contributor_id ) {

		$post_contributor_display_name = '';
		$post_contributor_description = '';
		$post_contributor_website = '';

		$post_contributor_display_name = get_the_author_meta( 'display_name', $contributor_id );

		 // If display name is not available then use nickname as display name.
		if ( empty( $post_contributor_display_name ) ) {
			 $post_contributor_display_name = get_the_author_meta( 'nickname', $contributor_id );
		}

		// Get biographical information or description.
		$post_contributor_description = get_the_author_meta( 'user_description', $contributor_id );

		// Get website URL.
		$post_contributor_website = get_the_author_meta( 'url', $contributor_id );

		$contributor_box = "<div class='wprtc_contributor_box_wrapper'>
													<div class='wprtc_contributor_gravatar'>
														<a href='" . get_author_posts_url( $contributor_id ) . "'>" . get_avatar( $contributor_id, 75 ) . "</a>
													</div>
													<div class='wprtc_contributor_details'>
														<p><a href='" . get_author_posts_url( $contributor_id ) . "'>" . $post_contributor_display_name . '</a></p>
														<p> ' . $post_contributor_description . '</p>
														<p> ' . $post_contributor_website . '</p>
													</div>
												</div>';

		return $contributor_box;
	}
}

new Wp_Rtcamp_Assignment_2b();
