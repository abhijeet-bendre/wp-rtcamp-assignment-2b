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
Description: Assignment-2b: WordPress-Contributors Plugin
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
		// Rolling in magic.
	}

}

new Wp_Rtcamp_Assignment_2b();
