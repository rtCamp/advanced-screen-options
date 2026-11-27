<?php
/**
 * Register Screen Options post type.
 *
 * @package AdvancedScreenOptions
 */

namespace AdvancedScreenOptions\Modules\Post_Types;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Advanced_Screen_Options
 */
class Advanced_Screen_Options extends Abstract_Post_Type {

	/**
	 * {@inheritDoc}
	 */
	public static function get_slug(): string {
		return 'adv-screen-options';
	}

	/**
	 * {@inheritDoc}
	 */
	public function register_post_type(): void {

		$labels = [
			'name'               => _x( 'Advanced Screen Options', 'post type general name', 'advanced-screen-options' ),
			'singular_name'      => _x( 'Advanced Screen Options', 'post type singular name', 'advanced-screen-options' ),
			'menu_name'          => _x( 'Advanced Screen Options', 'admin menu', 'advanced-screen-options' ),
			'name_admin_bar'     => _x( 'Advanced Screen Options', 'add new on admin bar', 'advanced-screen-options' ),
			'add_new'            => _x( 'Add New', 'Advanced Screen Options', 'advanced-screen-options' ),
			'add_new_item'       => __( 'Add New Advanced Screen Options', 'advanced-screen-options' ),
			'new_item'           => __( 'New Advanced Screen Options', 'advanced-screen-options' ),
			'edit_item'          => __( 'Edit Advanced Screen Options', 'advanced-screen-options' ),
			'view_item'          => __( 'View Advanced Screen Options', 'advanced-screen-options' ),
			'all_items'          => __( 'Advanced Screen Options', 'advanced-screen-options' ),
			'search_items'       => __( 'Search Advanced Screen Options', 'advanced-screen-options' ),
			'parent_item_colon'  => __( 'Parent Advanced Screen Options:', 'advanced-screen-options' ),
			'not_found'          => __( 'No Advanced Screen Options found.', 'advanced-screen-options' ),
			'not_found_in_trash' => __( 'No Advanced Screen Options found in trash.', 'advanced-screen-options' ),
		];

		$args = wp_parse_args(
			[
				'public'          => false,
				'show_ui'         => true,
				'has_archive'     => false,
				'show_in_rest'    => false,
				'supports'        => [ 'title' ],
				'show_in_menu'    => 'options-general.php',
				'labels'          => $labels,
				'capability_type' => 'adv_screen_option',
				'capabilities'    => [
					'edit_post'              => 'manage_options',
					'read_post'              => 'manage_options',
					'delete_post'            => 'manage_options',
					'edit_posts'             => 'manage_options',
					'edit_others_posts'      => 'manage_options',
					'publish_posts'          => 'manage_options',
					'read_private_posts'     => 'manage_options',
					'create_posts'           => 'manage_options',
					'delete_posts'           => 'manage_options',
					'delete_private_posts'   => 'manage_options',
					'delete_published_posts' => 'manage_options',
					'delete_others_posts'    => 'manage_options',
					'edit_private_posts'     => 'manage_options',
					'edit_published_posts'   => 'manage_options',
				],
				'map_meta_cap'    => false,
			],
			$this->default_args(),
		);

		// phpcs:ignore WordPress.NamingConventions.ValidPostTypeSlug.NotStringLiteral -- Slug is defined in get_slug method.
		register_post_type(
			self::get_slug(),
			$args
		);
	}
}
