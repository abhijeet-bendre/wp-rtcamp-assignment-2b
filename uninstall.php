<?php
/**
 * WordPress-Contributors Plugin Uninstallation
 *
 * Uninstalling deletes '_wprtc_contributors' post meta.
 *
 * @package rtCamp
 * @version 0.1
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

/**
 * If WPRTC_CONTRIBUTORS_DELETE_ALL_DATA constant is set to true in wp-config.php then only delete the associated data.
 */
function wprtc_contributors_delete_plugin_post_meta() {
	global $wpdb;

	if ( defined( 'WPRTC_CONTRIBUTORS_DELETE_ALL_DATA' ) && true === WPRTC_CONTRIBUTORS_DELETE_ALL_DATA ) {
			$wpdb->query( "DELETE FROM {$wpdb->postmeta}  WHERE  `meta_key` LIKE '_wprtc_contributors'" ); // db call ok; no-cache ok.
	}

}

wprtc_contributors_delete_plugin_post_meta();
