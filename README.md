![Banner](./wp-assets/banner-1544x500.png)

# Screen Options \- Manage WordPress Admin Screen Options and Column Visibility

**Contributors:** [rtCamp](https://profiles.wordpress.org/rtcamp/), [aviralmittal89](https://profiles.wordpress.org/aviralmittal89/), [aishwarryapande](https://profiles.wordpress.org/aishwarryapande/), [danish17](https://profiles.wordpress.org/iamdanih17/), [vishal4669](https://profiles.wordpress.org/vishal4669/), [up1512001](https://profiles.wordpress.org/up1512001/), [milindmore22](https://profiles.wordpress.org/milindmore22/)

**Tags:** WordPress, Screen Options, Admin Columns, Column Visibility, User Experience, Admin Interface

This plugin is licensed under the GPL v2 or later.

## Overview

Screen Options is a WordPress plugin that allows administrators to configure default screen options and column visibility across WordPress admin screens. Set site-wide defaults for screen options and optionally lock them to ensure consistency across all users and roles.

## Description

**Screen Options** simplifies the management of WordPress admin interface settings by allowing you to:

* **Set default screen options** for post types and admin screens
* **Configure column visibility** for list tables (posts, pages, custom post types)
* **Lock screen options** to prevent users from changing them
* **Manage settings per post type** or screen
* **Role-based screen options** for granular control

This makes it easier to maintain a consistent admin experience across your WordPress site and ensures users see only the columns and options they need.

## Why Screen Options?

WordPress's screen options are user-specific, meaning every user must configure their own preferences. For organizations managing WordPress sites, this can lead to:

- **Inconsistent user experiences** across team members
- **Training overhead** as new users need to configure their preferences
- **Support issues** when users accidentally hide important columns
- **Reduced productivity** from poorly configured admin screens

Screen Options solves this by:

- **Centralizing Configuration:** Set defaults once for all users
- **Enforcing Standards:** Lock settings to ensure consistency
- **Improving Onboarding:** New users get optimal settings immediately
- **Reducing Support:** Prevent common configuration issues

### Key Benefits

- **Improved user experience:** Ensure all users see the most relevant information
- **Time savings:** Configure once instead of for every user
- **Better training:** New users start with optimized settings
- **Consistency:** Maintain uniform admin interface across teams

## Features

### Core Functionality

- **Default Screen Options:** Set site-wide defaults for screen options
- **Column Management:** Control which columns are visible in admin list tables
- **Lock Settings:** Prevent users from modifying screen options
- **Post Type Support:** Configure settings per post type
- **Role-Based Options:** Different settings for different user roles
- **Easy Interface:** Simple, intuitive admin interface for configuration

### Preference Hierarchy

The plugin follows a clear preference hierarchy when applying screen settings:

1. **User-specific settings** (highest priority)
2. **Role-based defaults**
3. **Global site-wide defaults** (fallback)

This ensures that individual user preferences always take precedence, while administrators can still enforce consistent defaults across the site.

### Limitations
- Columns that are added by third-party plugins may be registered conditionally and may not appear in the Screen Options settings if those plugins are not active or their conditions are not met. (eg: Yoast SEO columns appear only when meta boxes are enabled in the Yoast settings)
- Columns that are not registered using standard WordPress APIs may not appear in the Screen Options settings.
- Some custom admin screens may not be fully compatible with the plugin.
- The plugin may not work as expected with heavily customized WordPress installations.
- Certain themes or plugins that modify admin screens may conflict with Screen Options.


## System Requirements

- **WordPress:** 6.8 or higher
- **Requires at least:** 6.0
- **Tested up to:** 6.9
- **Stable tag:** 1.0.0
- **PHP:** 8.1 or higher
- **Requires PHP:** 8.1

## Installation & Setup

For detailed installation instructions and configuration guides, please see our comprehensive [**Installation and Setup Guide**](./docs/INSTALLATION.md).

## Usage Guide

### Accessing the Settings

Navigate to **Default Screens** in your WordPress admin menu to access the screen options management interface.

### Configuring Default Screen Options

#### Setting Up Default Columns

1. Navigate to **Default Screens** in the WordPress admin
2. Click **"Add New"** to create a new screen options configuration
3. Select the **post type** or screen you want to configure
4. Choose which **columns** should be visible by default
5. Optionally **lock** the settings to prevent users from changing them
6. Click **"Publish"** to save your configuration

#### Managing Existing Configurations

- **Edit:** Click on any existing configuration to modify it
- **Delete:** Remove configurations that are no longer needed
- **Preview:** See how the settings will appear to users

### Role-Based Configuration

Configure different screen options for different user roles:

1. Create a new screen options configuration
2. Select the target **user role**
3. Configure the desired screen options
4. Publish to apply the settings

## Development & Contributing

Screen Options is actively developed and maintained by [rtCamp](https://rtcamp.com/).

- **Repository:** [https://github.com/rtCamp/screen-options](https://github.com/rtCamp/screen-options)  
- **Contributing Guide:** [docs/CONTRIBUTING.md](./docs/CONTRIBUTING.md)  
- **Development Guide:** [docs/DEVELOPMENT.md](./docs/DEVELOPMENT.md)

We welcome contributions! Please read our contributing guidelines before submitting pull requests.

## Frequently Asked Questions

### How do I set default screen options?

Create a new "Default Screen" entry, select the post type or screen, choose your desired columns and options, then publish.

### Can I lock screen options so users can't change them?

Yes, when configuring a screen option, you can enable the "lock" option to prevent users from modifying the settings.

### Does this work with custom post types?

Yes, the plugin supports all registered post types in WordPress, including custom post types.

### Can I configure different settings for different roles?

Yes, the plugin supports role-based screen options, allowing you to configure different settings for different user roles.

### Will this affect existing user preferences?

When you set defaults, new users will get those defaults. Existing users will retain their preferences unless you lock the settings.

### Does this work with multisite?

Yes, Screen Options is fully compatible with WordPress multisite installations. You can configure screen options for each site in your network independently.

### Is there an import/export feature for configurations?

Currently, import/export functionality is not built-in. However, configurations are stored as custom post types, so you can use standard WordPress export/import tools or migrate them with database migration plugins.

### What happens when I deactivate the plugin?

When you deactivate the plugin, users will revert to their individual screen option preferences or WordPress defaults. Your configurations remain in the database and will be reapplied if you reactivate the plugin.

### Does this affect front-end performance?

No, Screen Options only affects the WordPress admin area and has no impact on front-end performance. All functionality is loaded exclusively in the admin dashboard.

## Screenshots

![Screenshot-1](./wp-assets/screenshot-1.png)
Screen options admin menu.

![Screenshot-2](./wp-assets/screenshot-2.png)
Choose user role and post type, then select which columns are visible by default.

![Screenshot-3](./wp-assets/screenshot-3.png)
Locked columns prevent users from changing screen options column visibility.

![Screenshot-4](./wp-assets/screenshot-4.png)
Unlocked columns allow users to customize their column visibility.

## Troubleshooting

### Screen options not applying

- Verify the configuration is published
- Check that the post type matches
- Ensure there are no conflicting plugins

### Settings not locked

- Confirm the "lock" option is enabled in the configuration
- Clear any caches
- Check for JavaScript errors in the browser console

### Common Issues

- **Columns not showing:** Verify the columns are registered by the theme or plugins
- **Role-based settings not working:** Ensure the user has the correct role assigned
- **Changes not saving:** Check file permissions and database connectivity

## Support & Community

- **Support Forum:** [WordPress.org Support](https://wordpress.org/support/plugin/screen-options/)
- **Issues & Bug Reports:** [GitHub Issues](https://github.com/rtCamp/screen-options/issues)  

## License

This project is licensed under the GPL v2 or later \- see the [LICENSE](LICENSE) file for details.

---

**Made with ❤️ by [rtCamp](https://rtcamp.com/)**
