=== Screen Options ===
Contributors: rtCamp, aviralmittal89, aishwarryapande, iamdanih17, vishal4669, up1512001, milindmore22
Tags: screen options, admin columns, column visibility, user experience, admin interface
Requires at least: 6.8
Tested up to: 6.9
Stable tag: 1.0.0
Requires PHP: 8.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Manage WordPress admin screen options and column visibility. Set site-wide defaults and optionally lock them for consistency.

== Description ==

Screen Options is a WordPress plugin that allows administrators to configure default screen options and column visibility across WordPress admin screens. Set site-wide defaults for screen options and optionally lock them to ensure consistency across all users and roles.

**Why Screen Options?**

WordPress's screen options are user-specific, meaning every user must configure their own preferences. For organizations managing WordPress sites, this can lead to inconsistent user experiences, training overhead, and support issues when users accidentally hide important columns.

Screen Options solves this by providing centralized configuration of screen options, allowing you to set defaults once for all users or configure different settings for different user roles.

**Key Benefits:**

* **Improved User Experience:** Ensure all users see the most relevant information
* **Time Savings:** Configure once instead of for every user
* **Better Training:** New users start with optimized settings
* **Consistency:** Maintain uniform admin interface across teams

**Core Features:**

* **Default Screen Options:** Set site-wide defaults for screen options
* **Column Management:** Control which columns are visible in admin list tables
* **Lock Settings:** Prevent users from modifying screen options
* **Post Type Support:** Configure settings per post type
* **Role-Based Options:** Different settings for different user roles
* **Easy Interface:** Simple, intuitive admin interface for configuration

**Preference Hierarchy**

The plugin follows a clear preference hierarchy when applying screen settings:

1. **User-specific settings (highest priority)**
2. **Role-based defaults**
3. **Global site-wide defaults (fallback)**

This ensures that individual user preferences always take precedence, while administrators can still enforce consistent defaults across the site.

**Limitations**

* Columns that are added by third-party plugins may be registered conditionally and may not appear in the Screen Options settings if those plugins are not active or their conditions are not met. (eg: Yoast SEO columns appear only when meta boxes are enabled in the Yoast settings)
* Columns that are not registered using standard WordPress APIs may not appear in the Screen Options settings.
* Some custom admin screens may not be fully compatible with the plugin.
* The plugin may not work as expected with heavily customized WordPress installations.
* Certain themes or plugins that modify admin screens may conflict with Screen Options.


**Perfect for:**

* Organizations with multiple WordPress users
* Agencies setting up sites for clients
* Teams that need consistent admin interfaces
* Anyone who wants to improve the WordPress admin experience

== Installation ==

1. Upload the Screen Options plugin files to the `/wp-content/plugins/screen-options` directory, or install the plugin through the WordPress plugins screen directly
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Navigate to "Default Screens" in your WordPress admin menu
4. Click "Add New" to create a new screen options configuration
5. Select the post type or screen you want to configure
6. Choose which columns should be visible by default
7. Optionally enable "lock" to prevent users from changing these settings
8. Click "Publish" to save your configuration

For detailed installation and configuration instructions, see the [Installation Guide](https://github.com/rtCamp/screen-options/blob/main/docs/INSTALLATION.md).

== Frequently Asked Questions ==

= How do I set default screen options? =

Navigate to "Default Screens" in your admin menu, create a new entry, select the post type or screen, choose your desired columns and options, then publish.

= Can I lock screen options so users can't change them? =

Yes, when configuring a screen option, you can enable the "lock" option to prevent users from modifying the settings.

= Does this work with custom post types? =

Yes, the plugin supports all registered post types in WordPress, including custom post types.

= Can I configure different settings for different roles? =

Yes, the plugin supports role-based screen options, allowing you to configure different settings for different user roles.

= Will this affect existing user preferences? =

When you set defaults, new users will get those defaults. Existing users will retain their preferences unless you lock the settings.

= How do I remove a configuration? =

Navigate to "Default Screens", find the configuration you want to remove, and delete it like you would delete any post.

= Does this work with multisite? =

Yes, Screen Options is fully compatible with WordPress multisite installations. You can configure screen options for each site in your network independently.

= Is there an import/export feature for configurations? =

Currently, import/export functionality is not built-in. However, configurations are stored as custom post types, so you can use standard WordPress export/import tools or migrate them with database migration plugins.

= What happens when I deactivate the plugin? =

When you deactivate the plugin, users will revert to their individual screen option preferences or WordPress defaults. Your configurations remain in the database and will be reapplied if you reactivate the plugin.

= Does this affect front-end performance? =

No, Screen Options only affects the WordPress admin area and has no impact on front-end performance. All functionality is loaded exclusively in the admin dashboard.

== Screenshots ==

1. Screen options admin menu.
2. Choose user role and post type, then select which columns are visible by default
3. Locked columns prevent users from changing screen options column visibility
4. Unlocked columns allow users to customize their column visibility

== Changelog ==

= 1.0.0 =
* Initial release
* Default screen options configuration
* Column visibility management
* Lock settings functionality
* Role-based screen options
* Post type support
* Custom post type compatibility
* Initial release
* Default screen options configuration
* Column visibility management
* Lock settings functionality
* Role-based screen options
* Post type support
* Custom post type compatibility

== Upgrade Notice ==

= 1.0.0 =
Initial release of Screen Options. Configure default admin screen options for your WordPress site.

== Support ==

For Support: Forums on [WordPress.org](https://wordpress.org/support/plugin/screen-options)

== Contributing ==

Screen Options is open source and welcomes contributions. Visit our [GitHub repository](https://github.com/rtCamp/screen-options) to contribute code, report issues, or suggest features.

Development guidelines and contributing information can be found in our [repository documentation](https://github.com/rtCamp/screen-options/tree/main/docs).
