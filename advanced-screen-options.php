<?php
/**
 * Plugin Name: Advanced Screen Options
 * Description: Advanced Screen Options - Manage your admin screen options and column visibility with ease.
 * Author: rtCamp
 * Author URI: https://rtcamp.com
 * Plugin URI: https://github.com/rtCamp/advanced-screen-options/
 * License: GPL2+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: advanced-screen-options
 * Domain Path: /languages
 * Version: 1.2.0
 * Requires PHP: 8.1
 * Requires at least: 6.8
 * Tested up to: 7.0
 *
 * @package AdvancedScreenOptions
 */

namespace AdvancedScreenOptions;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit();

/**
 * Define the plugin constants.
 */
function constants(): void {
	/**
	 * Version of the plugin.
	 */
	define( 'ADVANCED_SCREEN_OPTIONS_VERSION', '1.2.0' );

	/**
	 * Root path to the plugin directory.
	 */
	define( 'ADVANCED_SCREEN_OPTIONS_DIR', plugin_dir_path( __FILE__ ) );

	/**
	 * Root URL to the plugin directory.
	 */
	define( 'ADVANCED_SCREEN_OPTIONS_URL', plugin_dir_url( __FILE__ ) );

	/**
	 * Plugin basename.
	 */
	define( 'ADVANCED_SCREEN_OPTIONS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
}

constants();

// If autoloader failed, we cannot proceed.
require_once __DIR__ . '/inc/Autoloader.php';
if ( ! \AdvancedScreenOptions\Autoloader::autoload() ) {
	return;
}

/**
 * Load plugin.
 */
if ( class_exists( 'AdvancedScreenOptions\Main' ) ) {
	add_action(
		'plugins_loaded',
		'\AdvancedScreenOptions\load_plugin'
	);
}

/**
 * Load AdvancedScreenOptions plugin functionality.
 *
 * @return void
 */
function load_plugin(): void {
	\AdvancedScreenOptions\Main::instance();

	//phpcs:ignore PluginCheck.CodeAnalysis.DiscouragedFunctions.load_plugin_textdomainFound -- @todo remove before submitting to .org.
	load_plugin_textdomain( 'advanced-screen-options', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
