<?php
/**
 * Plugin Name: Hoverboard Server
 * Description: Adds functionality for sites to run on Hoverboard server.
 * Version: 0.1
 * Author: Hoverboard
 * Author URI: https://hoverboardstudios.com
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

$server_vars = isset( $_SERVER ) ? $_SERVER : false;

// Set proper env var for env type.
if ( getenv( 'LANDO' ) ) {
	define( 'WP_ENVIRONMENT_TYPE', 'local' );

	if ( ! defined( 'WP_DEBUG' ) ) {
		define( 'WP_DEBUG', true );
	}

	if ( ! defined( 'WP_DEBUG_LOG' ) ) {
		define( 'WP_DEBUG_LOG', true );
	}

	if ( ! defined( 'WP_DEBUG_DISPLAY' ) ) {
		define( 'WP_DEBUG_DISPLAY', true );
	}
} elseif ( strstr( $server_vars['HOME'], 'hbserver.dev' ) ) {
	define( 'WP_ENVIRONMENT_TYPE', 'development' );

	if ( ! defined( 'WP_DEBUG' ) ) {
		define( 'WP_DEBUG', true );
	}

	if ( ! defined( 'WP_DEBUG_LOG' ) ) {
		define( 'WP_DEBUG_LOG', true );
	}

	if ( ! defined( 'WP_DEBUG_DISPLAY' ) ) {
		define( 'WP_DEBUG_DISPLAY', true );
	}
} elseif ( array_key_exists( 'SPINUPWP_SITE', $server_vars ) ) {
	define( 'WP_ENVIRONMENT_TYPE', 'production' );

	if ( ! defined( 'WP_DEBUG' ) ) {
		define( 'WP_DEBUG', false );
	}
}

// Spinup servers only.
if ( array_key_exists( 'SPINUPWP_SITE', $server_vars ) ) {
	define( 'DISALLOW_FILE_MODS', true );
	define( 'WP_AUTO_UPDATE_CORE', 'minor' );

	if ( ! defined( 'DISABLE_WP_CRON' ) ) {
		define( 'DISABLE_WP_CRON', true );
	}
}
/**
 * Set Blog Public
 *
 * @return boolean Is blog public.
 */
function hb_set_blog_public() {
	if ( wp_get_environment_type() === 'development' ) {
		return 0;
	} elseif ( wp_get_environment_type() === 'production' ) {
		return 1;
	}
}
add_filter( 'pre_option_blog_public', 'hb_set_blog_public', 0, 999 );

/**
 * Change robots.txt when the site is not set to public.
 *
 * @param  string  $output robots.txt output.
 * @param  boolean $public is public or not.
 *
 * @return  string robots.txt output
 */
function hb_robots_txt( $output, $public ) {
	if ( 0 === $public ) {
		$output = "User-agent: *\nDisallow: /\n\nUser-agent: RavenCrawler\nUser-agent: rogerbot\nUser-agent: dotbot\nUser-agent: SemrushBot\nUser-agent: SemrushBot-SA\nUser-agent: PowerMapper\nUser-agent: Swiftbot\nAllow: /";
	}
	return $output;
}
add_filter( 'robots_txt', 'hb_robots_txt', 99, 2 );

/**
 * Remove Yoast SEO Columns
 *
 * Credit: Andrew Norcross http://andrewnorcross.com/
 * Source: https://gist.github.com/amboutwe/18558a7e681e36c6bfe6e4fb647265ce
 *
 * If you have custom post types, you can add additional lines in this format
 * add_filter( 'manage_edit-{$post_type}_columns', 'yoast_seo_admin_remove_columns', 10, 1 );
 * replacing {$post_type} with the name of the custom post type.
 *
 * @param Array $columns Array of SEO Columns.
 *
 * @return  Array array of columns.
 */
function yoast_seo_admin_remove_columns( $columns ) {
	unset( $columns['wpseo-score-readability'] );
	unset( $columns['wpseo-title'] );
	unset( $columns['wpseo-metadesc'] );
	unset( $columns['wpseo-focuskw'] );
	unset( $columns['wpseo-links'] );
	unset( $columns['wpseo-linked'] );
	return $columns;
}
add_filter( 'manage_edit-post_columns', 'yoast_seo_admin_remove_columns', 10, 1 );
add_filter( 'manage_edit-tribe_events_columns', 'yoast_seo_admin_remove_columns', 10, 1 );
add_filter( 'manage_edit-page_columns', 'yoast_seo_admin_remove_columns', 10, 1 );
add_filter( 'manage_edit-staff_columns', 'yoast_seo_admin_remove_columns', 10, 1 );

/**
 * Activate/Deactivate required plugins.
 */
function hb_activate_plugins() {
	if ( wp_get_environment_type() === 'development' || wp_get_environment_type() === 'local' ) {
		deactivate_plugins(
			array(
				'bunnycdn/bunnycdn.php',
				'ewww-image-optimizer/ewww-image-optimizer.php',
			),
		);
	}

	activate_plugins(
		array(
			'display-environment-type/display-environment-type.php',
			'spinupwp/spinupwp.php',
			'wp-migrate-db-pro/wp-migrate-db-pro.php',
		),
	);
}
add_action( 'admin_init', 'hb_activate_plugins', 1 );
