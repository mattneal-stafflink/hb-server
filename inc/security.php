<?php
/**
 * Security-Related Tweaks
 *
 * @package hb_server
 */

add_filter( 'auto_core_update_send_email', '__return_false' );
add_filter( 'auto_plugin_update_send_email', '__return_false' );
add_filter( 'auto_theme_update_send_email', '__return_false' );
define( 'FORCE_SSL_ADMIN', true );

/**
 * Remove version number
 *
 * @return  string empty string
 */
function hb_version_remove_version() {
	return '';
}
add_filter( 'the_generator', 'hb_version_remove_version' );

// Spinup servers only.
if ( array_key_exists( 'SPINUPWP_SITE', $server_vars ) ) {
	define( 'DISALLOW_FILE_MODS', true );

	define( 'WP_AUTO_UPDATE_CORE', 'minor' );

	if ( ! defined( 'DISABLE_WP_CRON' ) ) {
		define( 'DISABLE_WP_CRON', true );
	}
}
