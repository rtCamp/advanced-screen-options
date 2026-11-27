<?php
/**
 * Enqueue assets for AdvancedScreenOptions.
 *
 * @package AdvancedScreenOptions
 */

namespace AdvancedScreenOptions\Modules\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AdvancedScreenOptions\Contracts\Interfaces\Registrable;

/**
 * Class Assets
 */
class Assets implements Registrable {
	/**
	 * The relative path to the built assets directory.
	 * No preceding or trailing slashes.
	 */
	private const ASSETS_DIR = 'build';

	/**
	 * Prefix for all asset handles.
	 */
	private const PREFIX = 'advanced-screen-options-';

	/**
	 * Asset handles
	 */
	public const ADMIN_STYLES_HANDLE = self::PREFIX . 'admin';

	/**
	 * Admin script handle.
	 */
	public const ADMIN_SCRIPTS_HANDLE = self::PREFIX . 'admin';

	/**
	 * Localized data for scripts.
	 *
	 * @var array<string,mixed>
	 */
	private static array $localized_data;

	/**
	 * Plugin directory path.
	 *
	 * @var string
	 */
	private string $plugin_dir;

	/**
	 * Plugin URL.
	 *
	 * @var string
	 */
	private string $plugin_url;

	/**
	 * Prepare localized data.
	 *
	 * @return array<string,mixed> Localized data array.
	 */
	public static function get_localized_data(): array {
		if ( empty( self::$localized_data ) ) {
			self::$localized_data = [];
		}

		return self::$localized_data;
	}

	/**
	 * Set Localized data.
	 *
	 * @param array<string,mixed> $data Data to merge into localized data.
	 */
	public static function set_localized_data( array $data ): void {
		self::$localized_data = array_merge( self::get_localized_data(), $data );
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->plugin_dir = (string) ADVANCED_SCREEN_OPTIONS_DIR;
		$this->plugin_url = (string) ADVANCED_SCREEN_OPTIONS_URL;
	}

	/**
	 * {@inheritDoc}
	 */
	public function register_hooks(): void {
		add_action( 'admin_enqueue_scripts', [ $this, 'register_assets' ], 20, 1 );

		// Add defer attribute to certain plugin bundles to improve admin load performance.
		add_filter( 'script_loader_tag', [ $this, 'defer_scripts' ], 10, 2 );
	}

	/**
	 * Register admin assets to WordPress.
	 *
	 * Assets are registered once centrally, and enqueued in the modules that need them.
	 */
	public function register_assets(): void {

		$this->register_style( self::ADMIN_STYLES_HANDLE, 'admin' );
		$this->register_script( self::ADMIN_SCRIPTS_HANDLE, 'admin' );

		wp_enqueue_style( self::ADMIN_STYLES_HANDLE );
	}

	/**
	 * Add defer attribute to certain plugin bundle scripts to improve loading performance.
	 *
	 * @param string $tag    The script tag.
	 * @param string $handle The script handle.
	 * @return string Modified script tag.
	 */
	public function defer_scripts( string $tag, string $handle ): string {
		$defer_handles = [
			self::ADMIN_SCRIPTS_HANDLE,
		];

		// Bail if we don't need to defer.
		if ( ! in_array( $handle, $defer_handles, true ) || false !== strpos( $tag, ' defer' ) ) {
			return $tag;
		}

		return str_replace( ' src', ' defer src', $tag );
	}

	/**
	 * Register a script.
	 *
	 * @param string   $handle    Name of the script. Should be unique.
	 * @param string   $filename  Path of the script relative to js directory.
	 *                            excluding the .js extension.
	 * @param string[] $deps      Optional. An array of registered script handles this script depends on. If not set, the dependencies will be inherited from the asset file.
	 * @param ?string  $ver       Optional. String specifying script version number, if not set, the version will be inherited from the asset file.
	 * @param bool     $in_footer Optional. Whether to enqueue the script before </body> instead of in the <head>.
	 */
	private function register_script( string $handle, string $filename, array $deps = [], $ver = null, bool $in_footer = true ): bool {
		if ( '' === $handle ) {
			return false;
		}

		$asset_file = sprintf( '%s/%s.asset.php', $this->plugin_dir . untrailingslashit( self::ASSETS_DIR ), $filename );

		// Bail if the asset file does not exist. Log error and optionally show admin notice.
		if ( ! file_exists( $asset_file ) ) {
			return false;
		}

		// phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable -- The file is checked for existence above.
		$asset = require_once $asset_file;

		$version   = $ver ?? ( $asset['version'] ?? filemtime( $asset_file ) );
		$asset_src = sprintf( '%s/%s.js', $this->plugin_url . untrailingslashit( self::ASSETS_DIR ), $filename );

		return wp_register_script(
			$handle,
			$asset_src,
			$deps ?: $asset['dependencies'],
			$version ?: false,
			$in_footer
		);
	}

	/**
	 * Register a CSS stylesheet
	 *
	 * @param string   $handle    Name of the stylesheet. Should be unique.
	 * @param string   $filename  Path of the stylesheet relative to the css directory,
	 *                            excluding the .css extension.
	 * @param string[] $deps      Optional. An array of registered stylesheet handles this stylesheet depends on. Default empty array.
	 * @param ?string  $ver       Optional. String specifying style version number, if not set, the version will be inherited from the asset file.
	 *
	 * @param string   $media     Optional. The media for which this stylesheet has been defined.
	 *                            Default 'all'. Accepts media types like 'all', 'print' and 'screen', or media queries like
	 *                            '(orientation: portrait)' and '(max-width: 640px)'.
	 */
	private function register_style( string $handle, string $filename, array $deps = [], $ver = null, string $media = 'all' ): bool {
		if ( '' === $handle ) {
			return false;
		}

		// CSS doesnt have a PHP assets file so we infer from the file itself.
		$asset_file = sprintf( '%s/%s.css', $this->plugin_dir . untrailingslashit( self::ASSETS_DIR ), $filename );

		// Bail if the asset file does not exist.
		if ( ! file_exists( $asset_file ) ) {
			return false;
		}

		$version   = $ver ?? (string) filemtime( $asset_file );
		$asset_src = sprintf( '%s/%s.css', $this->plugin_url . untrailingslashit( self::ASSETS_DIR ), $filename );

		// Register as a style.
		return wp_register_style(
			$handle,
			$asset_src,
			$deps,
			$version ?: false,
			$media
		);
	}
}
