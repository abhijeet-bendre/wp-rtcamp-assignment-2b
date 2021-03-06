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
Description: WordPress-Contributors Plugin, adds a new metabox, labeled "Contributors" to WordPress post-editor page. This metabox will display a list of authors (WordPress users) with their Gravatars and a checkbox for each author. Selected contributors are shown in frontend for posts for which they have contributed.

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

define( 'WPRTC_2B_CONTRIBUTORS_PLUGIN_NAME', 'wp-rtcamp-assignment-2b' );
define( 'WPRTC_2B_CONTRIBUTORS_PLUGIN_URL', trailingslashit( plugin_dir_url( __FILE__ ) ) );

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
		global $wp_filter;
		add_action( 'plugins_loaded', array( $this, 'wprtc_load_plugin_textdomain' ) );
		add_action( 'admin_init', array( $this, 'wprtc_simulate_admin_init' ), 1 );
		add_action( 'wprtc_admin_init', array( $this, 'wprtc_setup_contributors_metabox' ), 1 );
		add_action( 'admin_enqueue_scripts', array( $this, 'wprtc_init_assets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'wprtc_init_front_end_assets' ) );
		// Filter for appending contributors box on frontend.
		add_filter( 'the_content', array( $this, 'wprtc_append_contributors' ), 20 );

	}

	/**
	 * Simulate 'admin_init'
	 *
	 * @since 0.1
	 */
	public function wprtc_simulate_admin_init() {
		do_action( 'wprtc_admin_init' );
	}

	/**
	 * Load the plugin's translated strings, if available.
	 *
	 * @since 0.1
	 */
	public function wprtc_load_plugin_textdomain() {
		load_plugin_textdomain( 'wprtc_assignment_2b', false, basename( dirname( __FILE__ ) ) . '/languages/' );
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
		 * Check if $_GET['post_type'] exists or $pagenow is post-new.php or edit.php . (For "All Posts/ Add new Post" screen).
		 *	or
		 * Check if $_GET['post'] exists. (For Edit Post Screen).
		 */
		$post_type = isset( $_GET['post_type'] ) ? sanitize_text_field( wp_unslash( $_GET['post_type'] ) ) : ''; // Input var okay. WPCS: CSRF ok.
		$post_id = isset( $_GET['post'] ) ? sanitize_text_field( wp_unslash( $_GET['post'] ) ) : ''; // Input var okay. WPCS: CSRF ok.

		if ( ( 'post' === $post_type || in_array( $pagenow, array( 'post-new.php', 'edit.php' ), true ) )
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

		$show_metabox = $this->wprtc_show_contributors_metabox_to_whitelist_users();
		// Show Metabox and hook 'save_post' only for author, editor, admin.
		if ( $show_metabox ) {
			add_meta_box( 'wprtc_contributors',  __( 'Contributors', 'wprtc_assignment_2b' ), array( $this, 'wprtc_render_contributors_metabox' ), 'post', 'normal', 'low' );
		}

		// 'save_post' callback for saving selected_contributors.
		add_action( 'save_post', array( $this, 'wprtc_save_selected_contributors' ), 10 );
	}

	/**
	 * Show Metabox and hook 'save_post' only for author, editor, admin.
	 *
	 * @since 0.1
	 */
	public function wprtc_show_contributors_metabox_to_whitelist_users() {

		$whitelist_user_roles = array( 'author', 'editor', 'administrator' );
		$user = wp_get_current_user();
		$show_metabox = false;
		$user = wp_get_current_user();
		foreach ( $whitelist_user_roles as $role ) {
			if ( in_array( $role, (array) $user->roles, true ) && false === $show_metabox ) {
					$show_metabox = true;
			}
		}
		return $show_metabox;
	}

	/**
	 * Render contributors metabox.
	 *
	 * @since 0.1
	 */
	public function wprtc_render_contributors_metabox() {
		global $post;

		$all_users = get_users();
		$contributors_nonce = wp_create_nonce( '_wprtc_contributor_metabox_nonce' );

		$post_contributors = get_post_meta( $post->ID, '_wprtc_contributors' );

		if ( ! empty( $post_contributors ) ) {
			$post_contributors = $post_contributors[0];
		}

		if ( ! empty( $all_users ) ) {
			ob_start();

			/**
			* Post Author is by default skipped from Contributor Box (The User who Drafts / Publishes the post).
			* Contributors are additional users, apart from post Author. So If there are no Contributors, show a message to add them.
			*/
			$logged_in_user_id = get_current_user_id();
			if ( 1 === count( $all_users ) && $all_users[0]->ID === $logged_in_user_id ) {
				$admin_url = get_admin_url();
				echo esc_html__( 'No Contributors Found! ', 'wprtc_assignment_2b' );
				echo '<a target="_blank" href="' . esc_attr( $admin_url . 'users.php' ) . ' ">' . esc_html__( 'Please add from here', 'wprtc_assignment_2b' ) . '</a>';
				ob_get_flush();
				return;
			}

			echo "<table class='wprtc_contributors_table'>
							<thead class='wprtc_contributors_thead'>
								<tr>
									<th class='wprtc_table_cell_check'></th>
					 				<th>" . esc_html__( 'Name', 'wprtc_assignment_2b' ) . '</th>
									<th>' . esc_html__( 'Gravatar', 'wprtc_assignment_2b' ) . "</th>
					 			</tr>
							</thead>
						<tbody class='wprtc_contributors_tbody'>";
			foreach ( $all_users as $single_user ) {
				// Skip Post Author, since he is always a contributor.
				if ( (int) $post->post_author !== $single_user->ID ) {
					echo "<tr>
        					<th>
										<input type='checkbox' name='_wprtc_contributors[]' value='" . esc_attr( $single_user->ID ) . "' " . checked( in_array( (string) $single_user->ID, $post_contributors , true ), true, false ) . ">
									</th>
        					<td class='wprtc_table_username'>" . esc_html( $single_user->user_login ) . "</td>
									<td class='wprtc_table_gravator'>" . get_avatar( $single_user->ID, 50 ) . '</td>
        				</tr>';
				}
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

		$show_metabox = $this->wprtc_show_contributors_metabox_to_whitelist_users();

		/*
		* If doing auto save return.
		* or
		* Verify Nonce, if not verified return.
		* or
		* Show Metabox and hook 'save_post' only for author, editor, admin.
		*/
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ||
				 isset( $_POST['_wprtc_contributor_metabox_nonce'] ) && ! wp_verify_nonce( sanitize_key( $_POST['_wprtc_contributor_metabox_nonce'] ), '_wprtc_contributor_metabox_nonce' ) || // Input var okay.
				 ( ! $show_metabox )
			 ) {
			return;
		}

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
		// Check if page is single and 'post_type' is post.
		if ( is_single() && 'post' === $post->post_type ) {
			$content .= "<div class='wprtc_contributor_wrapper'>";
			$content .= "<p class='wprtc_title'>" . esc_html__( 'Contributors' , 'wprtc_assignment_2b' ) . ':</p>';
			$content .= $this->wprtc_display_contributors_box( $post->post_author );
			$post_contributors = get_post_meta( $post->ID, '_wprtc_contributors' );
			if ( ! empty( $post_contributors ) ) {
				$post_contributors = $post_contributors[0];
				foreach ( $post_contributors as $contributor_id ) {
					$content .= $this->wprtc_display_contributors_box( $contributor_id );
				}
			}
			$content .= '</div>';
			// Returns the content.
			return $content;
		}
	}

	/**
	 * Render Contributor info box.
	 *
	 * @param int $contributor_id Contains id of or post contributor.
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
