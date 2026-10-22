<?php
/**
 * Register Screen Options meta box.
 *
 * @package AdvancedScreenOptions
 */

namespace AdvancedScreenOptions\Modules\Meta;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AdvancedScreenOptions\Modules\Core\Cache;
use AdvancedScreenOptions\Modules\Post_Types\Advanced_Screen_Options;
use WP_Query;

/**
 * Class Screen_Options_Meta
 */
class Screen_Options_Meta extends Abstract_Meta_Box {

	/**
	 * {@inheritDoc}
	 */
	public static function get_id(): string {
		return 'screen_options_meta_box';
	}

	/**
	 * {@inheritDoc}
	 */
	public function register_meta_box(): void {
		add_meta_box(
			self::get_id(),
			__( 'Column Visibility Settings', 'advanced-screen-options' ),
			[ $this, 'render_meta_box' ],
			Advanced_Screen_Options::get_slug(),
			'normal',
			'default'
		);

		// Add admin notice for validation errors.
		add_action( 'admin_notices', [ $this, 'display_validation_notices' ] );
	}

	/**
	 * Render meta box content.
	 *
	 * @return void
	 */
	public function render_meta_box(): void {
		global $post;

		wp_nonce_field( 'screen_options_save_meta', 'screen_options_meta_nonce' );

		// Get all saved roles from post meta.
		$all_saved_roles = self::get_all_saved_roles();

		// Get role-post type configurations.
		$role_post_type_configs = self::get_role_post_type_configurations();

		// Get saved roles for this post.
		$saved_roles = [];
		if ( $post && $post->ID ) {
			$post_meta = Meta_Fields::get_roles( $post->ID );
			if ( ! empty( $post_meta ) ) {
				$saved_roles = $post_meta;
			}
		}

		$selected_post_type = '';
		if ( $post && $post->ID ) {
			$post_meta = Meta_Fields::get_post_type( $post->ID );
			if ( ! empty( $post_meta ) ) {
				$selected_post_type = $post_meta;
			}
		}

		// Get all roles except current saved role.
		$all_saved_roles = array_diff( $all_saved_roles, $saved_roles );

		// Get all available roles.
		$roles = wp_roles()->roles;
		?>
		<div class="admin-grid">

			<!-- LEFT COLUMN: Roles -->
			<div class="left-col">
				<div class="postbox">
					<div class="postbox-header">
						<h2><?php esc_html_e( 'Select Roles', 'advanced-screen-options' ); ?></h2>
						<span id="role-error" class="error-msg"><?php esc_html_e( 'Selection Required', 'advanced-screen-options' ); ?></span>
					</div>
					<div class="inside inside-flush">
						<div class="role-list">
							<div class="role-row">
								<input type="checkbox" value="all_users" name="screen_options_assigned_roles[]" id="role-all" class="role-check" data-target="all" <?php checked( in_array( 'all_users', $saved_roles, true ), true ); ?>>
								<label for="role-all"><?php esc_html_e( 'All Users', 'advanced-screen-options' ); ?></label>
								<div class="role-separator"></div>
							</div>
							<?php foreach ( $roles as $role_key => $role ) : ?>
								<?php
								$is_checked = in_array( $role_key, $saved_roles, true );
								?>
								<div class="role-row">
									<input type="checkbox" name="screen_options_assigned_roles[]" id="role-<?php echo esc_attr( $role_key ); ?>" class="role-check" value="<?php echo esc_attr( $role_key ); ?>" <?php checked( $is_checked, true, true ); ?>>
									<label for="role-<?php echo esc_attr( $role_key ); ?>"><?php echo esc_html( $role['name'] ); ?></label>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
			</div>

			<?php
				// Get all available post types except the screen options post type.
				$post_types = get_post_types(
					[
						'public'   => true,
						'_builtin' => false,
					],
					'objects',
					'or'
				);

				$columns = Meta_Fields::get_available_columns();

				// Get saved columns for this post.
				$saved_columns = [];
			if ( $post && $post->ID ) {
				$post_meta = Meta_Fields::get_columns( $post->ID );
				if ( ! empty( $post_meta ) ) {
					$saved_columns = $post_meta;
				}
			}

				// Remove screen options post type from the list.
				unset( $post_types[ Advanced_Screen_Options::get_slug() ] );
			?>
			<!-- RIGHT COLUMN: Settings -->
			<div id="settings-area" class="disabled">

				<!-- Toolbar with Post Type AND Global Lock -->
				<div class="selector-toolbar">
					<!-- Post Type Selector -->
					<div class="toolbar-group">
						<label for="post-type-select" class="label-strong"><?php esc_html_e( 'Post Type:', 'advanced-screen-options' ); ?></label>
						<select id="post-type-select" class="wp-select" name="screen_options_post_type">
							<option value=""><?php esc_html_e( 'Select Post Type', 'advanced-screen-options' ); ?></option>
							<?php
							foreach ( $columns as $post_type_key => $column ) {
								$post_type_key = str_replace( 'edit-', '', $post_type_key );
								if ( ! $post_type_key ) {
									continue;
								}

								// Build a list of roles that have already configured this post type.
								$configured_for_roles = [];
								foreach ( $role_post_type_configs as $role => $post_types ) {
									if ( ! in_array( $post_type_key, $post_types, true ) ) {
										continue;
									}

									$configured_for_roles[] = $role;
								}

								// Remove currently saved roles from configured roles to allow editing.
								$configured_for_roles = array_diff( $configured_for_roles, $saved_roles );

								// Create data attribute with configured roles (comma-separated).
								$data_configured_roles = ! empty( $configured_for_roles ) ? implode( ',', $configured_for_roles ) : '';
								?>
								<option value="<?php echo esc_attr( $post_type_key ); ?>"
									<?php selected( $post_type_key, $selected_post_type ); ?>
									data-configured-roles="<?php echo esc_attr( $data_configured_roles ); ?>"><?php echo esc_html( ucfirst( $post_type_key ) ); ?></option>
								<?php
							}
							?>
						</select>
						<span class="help-tip" data-tooltip="<?php esc_attr_e( 'Some post types may be disabled based on already existing configurations for selected roles.', 'advanced-screen-options' ); ?>">?</span>
					</div>

					<div class="toolbar-divider"></div>

				<?php
				// Get saved lock setting for this post.
				$is_locked = false;
				if ( $post && $post->ID ) {
					$is_locked = (bool) Meta_Fields::is_locked( $post->ID );
				}
				?>
					<!-- Global Lock Toggle -->
					<div class="toolbar-group">
						<label for="global-lock-check" class="label-strong-flex">
							<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
							<?php esc_html_e( 'Lock Screen Options', 'advanced-screen-options' ); ?>
						</label>
						<label class="wp-switch lock-switch">
							<input type="checkbox" id="global-lock-check" <?php checked( $is_locked, true, true ); ?> name="screen_options_lock_settings" value="1">
							<span class="slider"></span>
						</label>
						<span class="help-tip" data-tooltip="<?php esc_attr_e( 'If enabled, users cannot change any column visibility settings in their Screen Options tab.', 'advanced-screen-options' ); ?>">?</span>
					</div>
				</div>

				<?php
				// Settings Panels for each Post Type.
				foreach ( $columns as $post_type_key => $columns ) :
					$post_type_key = str_replace( 'edit-', '', $post_type_key );
					if ( ! $post_type_key ) {
						continue;
					}
					?>
				<!-- 1. Post Settings Panel -->
				<div id="panel-<?php echo esc_attr( $post_type_key ); ?>" class="settings-panel <?php echo esc_attr( $post_type_key === $selected_post_type ? 'active-panel' : '' ); ?>">
					<div class="postbox <?php echo esc_attr( true === $is_locked ? 'global-locked' : '' ); ?>" id="postbox-<?php echo esc_attr( $post_type_key ); ?>">
						<div class="postbox-header">
							<h2>
								<?php
								// translators: %s: Post Type Singular Name.
								printf( esc_html__( '%s Columns', 'advanced-screen-options' ), esc_html( ucfirst( $post_type_key ) ) );
								?>
								<span class="locked-badge" <?php echo esc_attr( true === $is_locked ? ' style=display:inline;' : '' ); ?>><?php esc_html_e( 'Configuration Locked', 'advanced-screen-options' ); ?></span>
							</h2>
						</div>

						<div class="inside">
							<div class="settings-grid">
								<?php

								foreach ( $columns as $column_id => $column_name ) :

									// Get all screen options columns for a post type and add checkboxes to enable/disable them also add a checkbox to lock/unlock them.
									if ( ! isset( $columns ) || ! is_array( $columns ) ) {
											continue;
									}

									// Skip checkbox and title columns.
									if ( 'cb' === $column_id || 'title' === $column_id ) {
										continue;
									}

									// Check if this column is saved for this post type.
									$is_checked = isset( $saved_columns[ $post_type_key ] ) && in_array( $column_id, $saved_columns[ $post_type_key ], true );
									// Default to checked if no saved data.
									if ( empty( $saved_columns ) ) {
										$is_checked = true;
									}
									?>
										<div class="field-group">
											<div class="tooltip-container">
											<span class="field-label"><?php echo esc_html( wp_strip_all_tags( $column_name ) ); ?></span>
										</div>
										<label class="wp-switch">
											<input type="checkbox" name="screen_options_columns[<?php echo esc_attr( $post_type_key ); ?>][<?php echo esc_attr( $column_id ); ?>]" value="<?php echo esc_attr( $column_id ); ?>" <?php checked( $is_checked, true, true ); ?>>
												<span class="slider"></span>
											</label>
										</div>
								<?php endforeach; ?>
							</div>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Save meta box data.
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public function save_meta_box_data( int $post_id ): void {

		// Verify if this is an auto save routine.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! isset( $_POST['screen_options_meta_nonce'] ) ) {
			return;
		}

		$nonce = sanitize_text_field( wp_unslash( $_POST['screen_options_meta_nonce'] ) );

		// Verify nonce.
		if ( ! wp_verify_nonce( $nonce, 'screen_options_save_meta' ) ) {
			return;
		}

		// Check post type.
		if ( get_post_type( $post_id ) !== Advanced_Screen_Options::get_slug() ) {
			return;
		}

		// Check user permissions.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Validate that post title is not empty.
		$post = get_post( $post_id );
		if ( empty( $post ) || empty( trim( $post->post_title ) ) ) {
			// Remove the hook to prevent infinite loop.
			remove_action( 'save_post', [ $this, 'save_meta_box_data' ] );

			// Prevent publishing by transitioning post to draft.
			$result = wp_update_post(
				[
					'ID'          => $post_id,
					'post_status' => 'draft',
				],
				true
			);

			// Check for errors.
			if ( is_wp_error( $result ) ) {
				// Re-add the hook before returning.
				add_action( 'save_post', [ $this, 'save_meta_box_data' ] );
				return;
			}

			// Set admin notice.
			Cache::set_transient(
				'screen_options_title_error_' . $post_id,
				__( 'Screen option cannot be saved! Title is required. Post has been set to draft and settings were not saved.', 'advanced-screen-options' ),
				45
			);

			// Re-add the hook.
			add_action( 'save_post', [ $this, 'save_meta_box_data' ] );
			return;
		}

		$columns       = [];
		$roles         = [];
		$lock_settings = false;

		// Save assigned roles (only selected ones).
		if ( isset( $_POST['screen_options_assigned_roles'] ) && is_array( $_POST['screen_options_assigned_roles'] ) ) {
			$roles = array_map( 'sanitize_text_field', wp_unslash( $_POST['screen_options_assigned_roles'] ) );
		}

		// Validate that at least one role is selected.
		if ( empty( $roles ) ) {
			// Remove the hook to prevent infinite loop.
			remove_action( 'save_post', [ $this, 'save_meta_box_data' ] );

			// Prevent publishing by transitioning post to draft.
			$result = wp_update_post(
				[
					'ID'          => $post_id,
					'post_status' => 'draft',
				],
				true
			);

			// Check for errors.
			if ( is_wp_error( $result ) ) {
				// Re-add the hook before returning.
				add_action( 'save_post', [ $this, 'save_meta_box_data' ] );
				return;
			}

			// Set admin notice.
			Cache::set_transient(
				'screen_options_role_error_' . $post_id,
				__( 'Screen option cannot be saved! At least one role must be selected before saving it. Settings saved as draft.', 'advanced-screen-options' ),
				45
			);
			return;
		}

		if ( isset( $_POST['screen_options_post_type'] ) ) {
			$selected_post_type = sanitize_text_field( wp_unslash( $_POST['screen_options_post_type'] ) );
		} else {
			$selected_post_type = '';
		}

		// Save columns for selected post type (only selected ones).
		if ( isset( $_POST['screen_options_columns'][ $selected_post_type ] ) && is_array( $_POST['screen_options_columns'][ $selected_post_type ] ) ) {
			$columns[ $selected_post_type ] = array_map(
				static function ( $column ) {
					return sanitize_text_field( $column );
				},
				wp_unslash( $_POST['screen_options_columns'][ $selected_post_type ] ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			);
		}

		// Save lock settings.
		if ( isset( $_POST['screen_options_lock_settings'] ) ) {
			$lock_settings_value = sanitize_text_field( wp_unslash( $_POST['screen_options_lock_settings'] ) );
			if ( '1' === $lock_settings_value ) {
				$lock_settings = true;
			}
		}

		Meta_Fields::bulk_update_post_meta(
			$post_id,
			[
				'roles'         => $roles,
				'columns'       => $columns,
				'lock_settings' => $lock_settings,
				'post_type'     => $selected_post_type,
			]
		);
	}

	/**
	 * Display validation error notices.
	 */
	public function display_validation_notices(): void {
		global $post;

		if ( ! $post || get_post_type( $post ) !== Advanced_Screen_Options::get_slug() ) {
			return;
		}

		// Check for title error.
		$title_error_message = Cache::get_transient( 'screen_options_title_error_' . $post->ID );
		if ( $title_error_message ) {
			printf(
				'<div class="notice notice-error is-dismissible"><p><strong>%s</strong></p></div>',
				esc_html( $title_error_message )
			);
			Cache::delete_transient( 'screen_options_title_error_' . $post->ID );
		}

		// Check for role error.
		$role_error_message = Cache::get_transient( 'screen_options_role_error_' . $post->ID );
		if ( ! $role_error_message ) {
			return;
		}

		printf(
			'<div class="notice notice-error is-dismissible"><p><strong>%s</strong></p></div>',
			esc_html( $role_error_message )
		);
		Cache::delete_transient( 'screen_options_role_error_' . $post->ID );
	}

	/**
	 * Get all role meta saved for all post types by meta key.
	 *
	 * @return array<string> Array of all saved roles.
	 */
	public static function get_all_saved_roles(): array {
		$all_roles = [];
		$posts     = new WP_Query(
			[
				'post_type'      => Advanced_Screen_Options::get_slug(),
				'posts_per_page' => 100,
				'fields'         => 'ids',
				'post_status'    => 'publish',
			]
		);

		if ( empty( $posts->posts ) ) {
			return [];
		}

		if ( ! is_array( $posts->posts ) ) {
			return [];
		}

		foreach ( $posts->posts as $post_id ) {
			if ( ! is_int( $post_id ) ) {
				continue;
			}
			$post_roles = Meta_Fields::get_roles( $post_id );
			if ( empty( $post_roles ) ) {
				continue;
			}

			$all_roles = array_merge( $all_roles, $post_roles );
		}
		return array_unique( $all_roles );
	}

	/**
	 * Add custom columns to post list table.
	 *
	 * @param array<string, string> $columns Existing columns.
	 *
	 * @return array<string, string> Modified columns.
	 */
	public function add_custom_post_columns( array $columns ): array {
		$columns['screen_options_roles']     = __( 'Roles', 'advanced-screen-options' );
		$columns['screen_options_post_type'] = __( 'Post Type', 'advanced-screen-options' );
		$columns['screen_options_columns']   = __( 'Columns Shown', 'advanced-screen-options' );
		$columns['screen_options_lock']      = __( 'Lock', 'advanced-screen-options' );
		return $columns;
	}

	/**
	 * Output content for custom columns.
	 *
	 * @param string $column  Column name.
	 * @param int    $post_id Post ID.
	 *
	 * @returns void
	 */
	public function custom_post_column_content( string $column, int $post_id ): void {

		// Check post type.
		if ( get_post_type( $post_id ) !== Advanced_Screen_Options::get_slug() ) {
			return;
		}

		switch ( $column ) {
			case 'screen_options_roles':
				$roles = Meta_Fields::get_roles( $post_id );
				if ( empty( $roles ) ) {
					echo esc_html__( 'No roles assigned', 'advanced-screen-options' );
					return;
				}

				// Format role names for display.
				$formatted_roles = array_map(
					static function ( $role ) {
						return ucfirst( str_replace( '_', ' ', $role ) );
					},
					$roles
				);

				// Convert to comma-separated string.
				$roles_string = implode( ', ', $formatted_roles );
				echo '<span class="role-badge">' . esc_html( $roles_string ) . '</span> ';
				break;

			case 'screen_options_post_type':
				$post_type = Meta_Fields::get_post_type( $post_id );
				if ( empty( $post_type ) ) {
					echo esc_html__( 'No post type selected', 'advanced-screen-options' );
					return;
				}
				echo esc_html( ucfirst( $post_type ) );
				break;

			case 'screen_options_columns':
				$columns = Meta_Fields::get_columns( $post_id );
				if ( empty( $columns ) ) {
					echo esc_html__( 'No columns configured', 'advanced-screen-options' );
					return;
				}
				$column_list = [];
				foreach ( $columns as $cols ) {
					if ( ! is_array( $cols ) ) {
						continue;
					}
					foreach ( $cols as $col ) {
						$column_list[] = $col;
					}
				}
				echo esc_html( implode( ', ', array_map( 'ucfirst', $column_list ) ) );
				break;

			case 'screen_options_lock':
				$is_locked = Meta_Fields::is_locked( $post_id );
				echo '<div class="column-icon-container">';
				if ( $is_locked ) {
					?>
					<svg class="lock-icon icon-locked" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
						<rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
						<path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
					</svg>
					<?php
				} else {
					?>
					<!-- Icon: Unlocked (Shown when unchecked) -->
					<svg class="lock-icon icon-unlocked" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
						<rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
						<path d="M7 11V7a5 5 0 0 1 9.9-1"></path>
					</svg>
					<?php
				}
				echo '</div>';
				break;
		}
	}

	/**
	 * Get all configured role-post type combinations.
	 *
	 * @return array<string, array<string>> Array with role slugs as keys and arrays of configured post types as values.
	 */
	public static function get_role_post_type_configurations(): array {
		$configurations = [];
		$posts          = new WP_Query(
			[
				'post_type'      => Advanced_Screen_Options::get_slug(),
				'posts_per_page' => 100,
				'fields'         => 'ids',
				'post_status'    => 'publish',
			]
		);

		if ( empty( $posts->posts ) ) {
			return [];
		}

		if ( ! is_array( $posts->posts ) ) {
			return [];
		}

		foreach ( $posts->posts as $post_id ) {
			if ( ! is_int( $post_id ) ) {
				continue;
			}

			$post_roles = Meta_Fields::get_roles( $post_id );
			$post_type  = Meta_Fields::get_post_type( $post_id );

			if ( empty( $post_roles ) || empty( $post_type ) ) {
				continue;
			}

			foreach ( $post_roles as $role ) {
				if ( ! isset( $configurations[ $role ] ) ) {
					$configurations[ $role ] = [];
				}
				$configurations[ $role ][] = $post_type;
			}
		}

		return $configurations;
	}
}
