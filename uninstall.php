<?php
/**
 * This will be executed when the plugin is uninstalled.
 *
 * @package AdvancedScreenOptions
 */

declare( strict_types=1 );

namespace AdvancedScreenOptions;

use AdvancedScreenOptions\Modules\Core\Cache;
use AdvancedScreenOptions\Modules\Post_Types\Advanced_Screen_Options;

// If uninstall not called from WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// To prevent fatal errors when plugin is deleted because ADVANCED_SCREEN_OPTIONS_DIR is declared in main plugin file.
if ( ! defined( 'ADVANCED_SCREEN_OPTIONS_DIR' ) ) {
	define( 'ADVANCED_SCREEN_OPTIONS_DIR', plugin_dir_path( __FILE__ ) );
}

// Bootstrap the autoloader so classes are available during uninstall.
require_once ADVANCED_SCREEN_OPTIONS_DIR . 'inc/Autoloader.php';
\AdvancedScreenOptions\Autoloader::autoload();

/**
 * The (site-specific) uninstall function.
 */
function uninstall(): void {
	// Delete all screen option posts and associated meta.
	delete_all_screen_option_post_types();

	// Clear all plugin transients.
	delete_all_transients();

	// Delete plugin options.
	delete_plugin_options();
}

/**
 * Delete all screen option posts and their metadata.
 */
function delete_all_screen_option_post_types(): void {
	$failed_post_ids = [];

	while ( true ) {
		$args = [
			'post_type'        => Advanced_Screen_Options::get_slug(),
			'post_status'      => 'any',
			'numberposts'      => 100,
			'fields'           => 'ids',
			'suppress_filters' => false,
		];

		if ( ! empty( $failed_post_ids ) ) {
			$args['post__not_in'] = $failed_post_ids; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_post__not_in, WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in -- Exclude failed IDs so batch deletion makes progress during uninstall.
		}

		$screen_option_posts = get_posts( $args ); // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.get_posts_get_posts

		if ( empty( $screen_option_posts ) ) {
			break;
		}

		foreach ( $screen_option_posts as $post_id ) {
			if ( ! is_int( $post_id ) ) {
				continue;
			}

			// Delete post meta first.
			delete_post_meta( $post_id, 'screen_options_roles' );
			delete_post_meta( $post_id, 'screen_options_post_type' );
			delete_post_meta( $post_id, 'screen_options_columns' );
			delete_post_meta( $post_id, 'screen_options_lock' );

			// Delete the post permanently.
			$deleted = wp_delete_post( $post_id, true );

			if ( ! $deleted ) {
				$failed_post_ids[] = $post_id;
				continue;
			}
		}
	}
}

/**
 * Delete all plugin transients.
 */
function delete_all_transients(): void {
	// Use the Cache class method to clear all transients.
	Cache::clear_all_transients();
}

/**
 * Delete all plugin options.
 */
function delete_plugin_options(): void {
	// Delete available columns option.
	delete_option( 'screen_options_available_columns' );

	// Delete any other plugin-specific options.
	// Add more as needed.
}

uninstall();
