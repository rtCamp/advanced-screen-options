<?php
/**
 * Role based screen options assignments.
 *
 * @package AdvancedScreenOptions
 */

namespace AdvancedScreenOptions\Modules\Column_Assignment;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AdvancedScreenOptions\Contracts\Interfaces\Registrable;
use AdvancedScreenOptions\Modules\Core\Assets;
use AdvancedScreenOptions\Modules\Meta\Meta_Fields;
use AdvancedScreenOptions\Modules\Post_Types\Advanced_Screen_Options;
use WP_Query;

/**
 * Class for Role based Screen option assignment.
 */
class Role_Based_Screen_Options implements Registrable {

	/**
	 * Screen Options Posts ID.
	 *
	 * @var int
	 */
	private int $screen_options_posts_id = 0;

	/**
	 * {@inheritDoc}
	 */
	public function register_hooks(): void {
		// Use the 'hidden_columns' filter to set default hidden columns based on user role.
		add_filter( 'hidden_columns', [ $this, 'filter_hidden_columns' ], 10, 3 );

		// Enqueue admin scripts.
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ], 25 );
	}

	/**
	 * Filter hidden columns based on user role.
	 *
	 * @param array      $hidden_columns Array of hidden column IDs.
	 * @param \WP_Screen $screen         Current screen object.
	 * @param bool       $use_defaults   Whether to use default hidden columns.
	 *
	 * @return array Modified array of hidden column IDs.
	 */
	public function filter_hidden_columns( $hidden_columns, $screen, $use_defaults ): array {

		// If use_defaults is false, return original hidden columns.
		if ( ! $use_defaults ) {
			return $hidden_columns;
		}

		$this->screen_options_posts_id = $this->get_screen_options_post_id();

		if ( 0 === $this->screen_options_posts_id ) {
			return $hidden_columns;
		}

		$selected_columns = Meta_Fields::get_columns( $this->screen_options_posts_id );

		// If no selected columns found, return original hidden columns.
		if ( empty( $selected_columns ) ) {
			return $hidden_columns;
		}

		$columns_for_screen = [];

		// Get current columns for the screen.
		$current_columns = array_keys( get_column_headers( $screen->id ) );

		// Determine screen ID key for selected columns.
		$screen_id_key = \str_replace( 'edit-', '', $screen->id );

		if ( isset( $selected_columns[ $screen_id_key ] ) ) {
			$columns_for_screen = $selected_columns[ $screen_id_key ];
		}

		if ( empty( $columns_for_screen ) ) {
			return $hidden_columns;
		}

		$columns_for_screen[] = 'title';
		$cb_column_key        = array_search( 'cb', $current_columns, true );

		if ( false !== $cb_column_key ) {
			unset( $current_columns[ $cb_column_key ] ); // Remove checkbox column if present.
		}

		// Determine columns to hide.
		$columns_to_hide = array_diff( $current_columns, $columns_for_screen );

		// If there are columns to hide, return them.
		if ( ! empty( $columns_to_hide ) ) {
			return $columns_to_hide;
		}

		return $hidden_columns;
	}

	/**
	 * Function to retrieve current screen options id based on user role.
	 *
	 * @return int Screen Options Post ID.
	 */
	public function get_screen_options_post_id(): int {

		if ( $this->screen_options_posts_id > 0 ) {
			return $this->screen_options_posts_id;
		}

		$current_user = wp_get_current_user();
		$user_roles   = (array) $current_user->roles;

		if ( empty( $user_roles ) ) {
			return 0;
		}

		// Get current post type.
		$screen            = get_current_screen();
		$current_post_type = '';
		if ( ! empty( $screen ) ) {
			if ( ! empty( $screen->post_type ) ) {
				$current_post_type = $screen->post_type;
			} elseif ( ! empty( $screen->id ) && 0 === strpos( $screen->id, 'edit-' ) ) {
				$current_post_type = str_replace( 'edit-', '', $screen->id );
			}
		}

		// Build meta query to fetch screen options posts based on all user roles.
		$role_meta_query = []; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query

		foreach ( $user_roles as $role ) {
			$role_meta_query[] = [
				'key'     => 'screen_options_roles',
				'value'   => $role,
				'compare' => 'LIKE',
			];
		}

		if ( count( $role_meta_query ) > 1 ) {
			$role_meta_query['relation'] = 'OR';
		}

		$meta_query = [];
		if ( ! empty( $current_post_type ) ) {
			$meta_query = [
				'relation' => 'AND',
				[
					'key'     => 'screen_options_post_type',
					'value'   => $current_post_type,
					'compare' => '=',
				],
				$role_meta_query,
			];
		} else {
			$meta_query = $role_meta_query;
		}

		// Fetch screen options posts based on user role.
		$matched_post_id = $this->find_screen_options_post_id( $meta_query, $current_post_type );

		// If no posts found for the roles, check for 'all_users'.
		if ( 0 === $matched_post_id ) {
			$all_users_meta = [
				[
					'key'     => 'screen_options_roles',
					'value'   => 'all_users',
					'compare' => 'LIKE',
				],
			];

			if ( ! empty( $current_post_type ) ) {
				$all_users_meta_query = [
					'relation' => 'AND',
					[
						'key'     => 'screen_options_post_type',
						'value'   => $current_post_type,
						'compare' => '=',
					],
					$all_users_meta,
				];
			} else {
				$all_users_meta_query = $all_users_meta;
			}

			$matched_post_id = $this->find_screen_options_post_id( $all_users_meta_query, $current_post_type );
		}

		$this->screen_options_posts_id = $matched_post_id;

		return $this->screen_options_posts_id;
	}

	/**
	 * Find a matching screen options post ID for a given meta query and post type.
	 *
	 * Traverses matching posts with bounded pagination to find the screen options post
	 * matching the current screen's post type.
	 *
	 * @param array  $meta_query        Meta query arguments.
	 * @param string $current_post_type Current screen post type.
	 *
	 * @return int Matching post ID, or 0 if none found.
	 */
	private function find_screen_options_post_id( array $meta_query, string $current_post_type ): int {
		$paged = 1;

		do {
			$query = new WP_Query(
				[
					'post_type'      => Advanced_Screen_Options::get_slug(),
					'posts_per_page' => 100,
					'paged'          => $paged,
					'meta_query'     => $meta_query, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					'fields'         => 'ids',
					'post_status'    => 'publish',
					'no_found_rows'  => false,
				]
			);

			if ( empty( $query->posts ) || ! is_array( $query->posts ) ) {
				break;
			}

			foreach ( $query->posts as $post_id ) {
				if ( ! is_int( $post_id ) ) {
					continue;
				}

				if ( empty( $current_post_type ) ) {
					return $post_id;
				}

				$assigned_post_type = Meta_Fields::get_post_type( $post_id );
				if ( $current_post_type === $assigned_post_type ) {
					return $post_id;
				}
			}

			++$paged;
		} while ( $paged <= $query->max_num_pages );

		return 0;
	}

	/**
	 * Get lock settings for the current role.
	 *
	 * @return bool
	 */
	public function get_lock_settings_for_current_role(): bool {

		$screen = get_current_screen();

		if ( empty( $screen ) || empty( $screen->id ) ) {
			return false;
		}

		$hidden = get_user_option( 'manage' . $screen->id . 'columnshidden' );

		$use_defaults = ! is_array( $hidden );

		if ( ! $use_defaults ) {
			return false;
		}

		// Get the screen options post ID for the current role.
		$screen_options_post_id = $this->get_screen_options_post_id();

		// If no screen options post found, return false.
		if ( 0 === $screen_options_post_id ) {
			return false;
		}

		// Get the post type assigned to this screen options post.
		$screen_post_type = Meta_Fields::get_post_type( $screen_options_post_id );

		// If screen post type is set and does not match current screen's post type, return false.
		if ( ! empty( $screen_post_type ) && $screen_post_type !== $screen->post_type ) {
			return false;
		}

		$is_locked = Meta_Fields::is_locked( $screen_options_post_id );

		return ! empty( $is_locked );
	}

	/**
	 * Enqueue admin scripts.
	 *
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public function enqueue_scripts( string $hook ) {

		// Add only on post listing pages.
		if ( strpos( $hook, 'edit.php' ) === false && strpos( $hook, 'post.php' ) === false && strpos( $hook, 'post-new.php' ) === false ) {
			return;
		}

		wp_enqueue_script( Assets::ADMIN_SCRIPTS_HANDLE );

		// Add lock settings to localized script data.
		Assets::set_localized_data(
			[
				'is_locked'   => $this->get_lock_settings_for_current_role(),
				'lockMessage' => \sprintf(
					'<strong>%1$s</strong> %2$s %3$s',
					/* translators: 1: Screen Options */
					__( 'Locked:', 'advanced-screen-options' ),
					__( 'columns are locked by the administrator. To modify your screen options, ', 'advanced-screen-options' ),
					\current_user_can( 'manage_options' ) ?
						__( 'go to settings -> default screen options.', 'advanced-screen-options' ) :
						__( 'please contact your site administrator.', 'advanced-screen-options' )
				),
			]
		);

		// Localize the script with updated data.
		wp_localize_script(
			Assets::ADMIN_SCRIPTS_HANDLE,
			'ScreenOptionsSettings',
			Assets::get_localized_data()
		);
	}
}
