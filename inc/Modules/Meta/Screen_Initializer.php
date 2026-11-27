<?php
/**
 * Initialize admin screens in background to capture default columns.
 *
 * @package AdvancedScreenOptions
 */

namespace AdvancedScreenOptions\Modules\Meta;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AdvancedScreenOptions\Contracts\Interfaces\Registrable;
use AdvancedScreenOptions\Modules\Core\Cache;
use WP_Screen;

/**
 * Class Screen_Initializer
 *
 * This class initializes admin screens in the background to ensure all columns
 * are registered and available for capture, even when not on that specific screen.
 */
class Screen_Initializer implements Registrable {

	/**
	 * {@inheritDoc}
	 */
	public function register_hooks(): void {
		// Use transient-based caching to limit frequency of initialization.
		// Priority 999 ensures other plugins have registered their column hooks first
		// (e.g., Yoast SEO registers at priority 10 via setup_hooks).
		add_action( 'admin_init', [ $this, 'maybe_initialize_screens' ], 9999 );

		// Allow forcing a refresh via query parameter (admin only).
		add_action( 'admin_init', [ $this, 'force_clear_transient' ], 1 );

		// On plugin activation and deactivation of any plugin, clear the transient to force re-initialization.
		add_action( 'activated_plugin', [ $this, 'clear_transient_initialize_screens' ] );
		add_action( 'deactivated_plugin', [ $this, 'clear_transient_initialize_screens' ] );

		// On plugin or theme update, clear the transient to force re-initialization.
		add_action( 'upgrader_process_complete', [ $this, 'clear_transient_initialize_screens' ] );

		// On theme switch, clear the transient to force re-initialization.
		add_action( 'after_switch_theme', [ $this, 'clear_transient_initialize_screens' ] );
	}

	/**
	 * Force clear transient if requested via query parameter.
	 *
	 * @return void
	 */
	public function force_clear_transient(): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! isset( $_GET['screen_options_refresh'] ) || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Clear saved available columns to force re-capture.
		Meta_Fields::delete_available_columns();

		// Clear the transient to force re-initialization.
		$this->clear_transient_initialize_screens();
	}

	/**
	 * Clear the transient to force re-initialization of screens.
	 *
	 * @return void
	 *
	 * @see maybe_initialize_screens()
	 */
	public function clear_transient_initialize_screens(): void {
		// Clear the transient to force re-initialization on next admin_init.
		Cache::delete_transient( 'screen_options_last_column_scan' );
	}

	/**
	 * Maybe initialize screens based on transient cache.
	 *
	 * This method uses a transient to limit how often the full initialization runs.
	 * It only runs once per day unless forced.
	 *
	 * @return void
	 */
	public function maybe_initialize_screens(): void {

		// Check if we've run this recently (within 24 hours) or if forced.
		$last_run = Cache::get_transient( 'screen_options_last_column_scan' );

		// skip if ran recently and not forced.
		if ( false !== $last_run ) {
			return;
		}

		// Set transient for 24 hours to prevent running too frequently.
		Cache::set_transient( 'screen_options_last_column_scan', time(), DAY_IN_SECONDS );

		// Run the full initialization.
		$this->initialize_screens_for_columns();
	}

	/**
	 * Get currently active post types supported by screen options.
	 *
	 * @return array<string, \WP_Post_Type> Associative array of post type objects keyed by post type slug.
	 */
	public static function get_active_post_types(): array {
		// Get all public custom post types.
		$post_types = get_post_types(
			[
				'public'   => true,
				'_builtin' => false,
			],
			'objects'
		);

		// Add built-in post types.
		$builtin_post_types = [ 'post', 'page' ];
		foreach ( $builtin_post_types as $builtin_type ) {
			if ( ! post_type_exists( $builtin_type ) ) {
				continue;
			}

			$post_type_object = get_post_type_object( $builtin_type );
			if ( null !== $post_type_object ) {
				$post_types[ $builtin_type ] = $post_type_object;
			}
		}

		return $post_types;
	}

	/**
	 * Initialize edit screens in the background to capture default columns.
	 *
	 * This method creates virtual admin screens for post types so that all
	 * column filters are triggered and columns can be recorded.
	 *
	 * @return void
	 */
	public function initialize_screens_for_columns(): void {
		global $typenow, $current_screen, $pagenow, $hook_suffix, $post_type;

		// Store current values to restore later.
		$original_typenow     = $typenow;
		$original_screen      = $current_screen;
		$original_pagenow     = $pagenow;
		$original_hook_suffix = $hook_suffix;
		$original_post_type   = $post_type;

		// Get currently active post types.
		$post_types = self::get_active_post_types();

		// Initialize each screen and capture columns.
		$active_screen_ids = [];
		foreach ( $post_types as $p_type ) {
			if ( null === $p_type ) {
				continue;
			}

			$active_screen_ids[ 'edit-' . $p_type->name ] = true;
			$this->initialize_edit_screen( $p_type->name );
		}

		// Prune any previously scanned post types that are no longer active.
		$saved_columns = Meta_Fields::get_available_columns();
		if ( ! empty( $saved_columns ) ) {
			$active_columns = array_intersect_key( $saved_columns, $active_screen_ids );
			if ( count( $active_columns ) !== count( $saved_columns ) ) {
				Meta_Fields::update_available_columns( $active_columns );
			}
		}

		// Restore original values.
		$typenow        = $original_typenow; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited, SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
		$current_screen = $original_screen; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited, SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
		$pagenow        = $original_pagenow; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited, SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
		$hook_suffix    = $original_hook_suffix; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited, SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
		$post_type      = $original_post_type; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited, SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
	}

	/**
	 * Initialize a specific edit screen to register columns.
	 *
	 * @param string $post_type The post type to initialize.
	 * @return void
	 */
	private function initialize_edit_screen( string $post_type ): void {
		global $typenow, $pagenow, $hook_suffix, $wp_query, $post;

		// Store original global post to restore later.
		$original_post = $post;

		// Set the global post type and context to simulate edit.php.
		$typenow              = $post_type; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$GLOBALS['post_type'] = $post_type; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$pagenow              = 'edit.php'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited, SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
		$hook_suffix          = 'edit.php'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited, SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable

		// Create a fake post object with the correct post_type to make get_post_type() return the correct value.
		// This is essential for plugins like Co-Authors Plus that call get_post_type() without passing a post ID.
		$post = (object) [  // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			'ID'        => 0,
			'post_type' => $post_type,
		];

		// Set query vars to ensure get_post_type() returns the correct post type.
		// This is crucial for plugins like Co-Authors Plus that call get_post_type() to check enabled post types.
		if ( ! empty( $wp_query ) ) {
			$wp_query->set( 'post_type', $post_type );
		}

		// Also set $_GET['post_type'] as a fallback for plugins that check this directly.
		$_GET['post_type'] = $post_type; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		// Create a screen object for this post type's edit screen.
		$screen = WP_Screen::get( 'edit-' . $post_type );

		if ( ! $screen ) {
			// Restore original post.
			$post = $original_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			return;
		}

		// Temporarily set as current screen to trigger column filters.
		$screen->set_current_screen();

		// Fire the load-{$pagenow} hook to trigger third-party plugin hooks.
		// This is crucial for plugins like Co-Authors Plus that register columns on this hook.
		do_action( 'load-edit.php' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound, WordPress.NamingConventions.ValidHookName.UseUnderscores, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound

		// Load the list table class to trigger column registration.
		$list_table = $this->load_list_table( $post_type );

		// Capture and store the columns.
		$this->capture_columns( $screen, $list_table );

		// Restore the original post object.
		$post = $original_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited, SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
	}

	/**
	 * Load the list table for a post type to register columns.
	 *
	 * @param string $post_type The post type.
	 * @return \WP_Posts_List_Table|null
	 */
	private function load_list_table( string $post_type ): ?\WP_Posts_List_Table {
		// Don't actually include the files, just trigger the column filters.
		if ( ! function_exists( '_get_list_table' ) ) {
			require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
			require_once ABSPATH . 'wp-admin/includes/class-wp-posts-list-table.php';
		}

		try {
			// Create list table instance to trigger column registration.
			// We use output buffering to suppress any output.
			ob_start();
			$list_table = _get_list_table( 'WP_Posts_List_Table', [ 'screen' => 'edit-' . $post_type ] );

			if ( $list_table ) {
				// Trigger column registration.
				$list_table->get_columns();
			}
			ob_end_clean();

			return $list_table;
		} catch ( \Throwable $e ) {
			// Silently fail if there's an issue.
			if ( ob_get_level() > 0 ) {
				ob_end_clean();
			}

			return null;
		}
	}

	/**
	 * Capture and store columns for a screen.
	 *
	 * @param \WP_Screen                $screen The screen object.
	 * @param \WP_Posts_List_Table|null $list_table The list table object.
	 * @return void
	 */
	private function capture_columns( WP_Screen $screen, ?\WP_Posts_List_Table $list_table = null ): void {
		// Temporarily remove all filters to get raw columns.
		remove_all_filters( 'default_hidden_columns' );
		remove_all_filters( 'hidden_columns' );

		// Get columns using WordPress core function.
		$columns = get_column_headers( $screen );

		$custom_columns = _get_list_table( 'WP_Posts_List_Table', [ 'screen' => $screen->id ] )->get_columns();
		$columns        = array_merge( $columns, $custom_columns );

		if ( empty( $columns ) ) {
			return;
		}

		// Get currently saved columns.
		$saved_columns = Meta_Fields::get_available_columns();

		if ( empty( $saved_columns ) ) {
			$saved_columns = [];
		}

		// Store columns with both keys and labels.
		$column_data = [];
		foreach ( $columns as $column_key => $column_label ) {
			$column_data[ $column_key ] = $column_label;
		}

		// Check if we need to update.
		$needs_update = false;

		if ( ! array_key_exists( $screen->id, $saved_columns ) ) {
			$needs_update = true;
		} else {
			// Check if columns have changed.
			$current_keys = array_keys( $column_data );
			$saved_keys   = is_array( $saved_columns[ $screen->id ] )
				? array_keys( $saved_columns[ $screen->id ] )
				: [];

			$added_keys   = array_diff( $current_keys, $saved_keys );
			$removed_keys = array_diff( $saved_keys, $current_keys );
			if ( ! empty( $added_keys ) || ! empty( $removed_keys ) ) {
				$needs_update = true;
			}
		}

		// Update if needed.
		if ( ! $needs_update ) {
			return;
		}

		$saved_columns[ $screen->id ] = $column_data;
		Meta_Fields::update_available_columns( $saved_columns );
	}
}
