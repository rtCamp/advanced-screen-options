# Advanced Screen Options - Installation and Setup Guide

This guide provides detailed instructions for installing and configuring the Advanced Screen Options plugin for your WordPress site.

## Installation Overview

The installation process involves the following key steps:

1. Download and install the plugin
2. Activate the plugin
3. Configure default screen options

## Step 1: Download and Install Plugin

### Option A: Install from WordPress.org (When Available)

1. Navigate to **Plugins → Add New** in your WordPress admin
2. Search for "Advanced Screen Options"
3. Click **Install Now** and then **Activate**

### Option B: Manual Installation

1. Download the latest Advanced Screen Options plugin from [GitHub Releases](https://github.com/rtCamp/advanced-screen-options/releases)
2. Upload the plugin files to `/wp-content/plugins/advanced-screen-options/` directory
3. Navigate to **Plugins** in your WordPress admin and activate "Advanced Screen Options"

### Option C: Install from Source Code

If installing from source code, run the following commands in the plugin directory:

```bash
composer install --no-dev --optimize-autoloader
npm install
npm run build:prod
```

## Step 2: Activate the Plugin

1. Navigate to **Plugins** in your WordPress admin
2. Find "Advanced Screen Options" in the plugin list
3. Click **Activate**

Upon activation, the plugin will:
- Initialize screen options detection for all registered post types
- Register the "Advanced Screen Options" custom post type under **Settings → Advanced Screen Options**

## Step 3: Configure Default Screen Options

### Creating Your First Configuration

1. Navigate to **Settings → Advanced Screen Options** in your WordPress admin menu
2. Click **Add New** to create a new screen options configuration
3. **Title:** Give your configuration a descriptive name (e.g., "Posts Default Columns")
4. **Select Post Type/Screen:** Choose the screen you want to configure (e.g., Posts, Pages, Custom Post Type)
5. **Configure Columns:** Check the columns you want visible by default
6. **Lock Settings (Optional):** Enable if you want to prevent users from changing these settings
7. Click **Publish** to save your configuration

### Configuring Role-Based Screen Options

1. Navigate to **Settings → Advanced Screen Options → Add New**
2. Select the target **user role** (e.g., Editor, Author, Contributor)
3. Choose the post type or screen
4. Configure the desired screen options
5. Publish to apply the settings

## Configuration Examples

### Example 1: Configure Default Columns for Posts

1. Go to **Settings → Advanced Screen Options → Add New**
2. Title: "Posts - Default Columns"
3. Select: Post Type → Posts
4. Enable columns: Title, Author, Categories, Tags, Date
5. Lock settings: Yes (to prevent users from changing)
6. Publish

### Example 2: Configure Editor Role Columns

1. Go to **Settings → Advanced Screen Options → Add New**
2. Title: "Editor - Posts Columns"
3. Select: User Role → Editor
4. Select: Post Type → Posts
5. Enable columns: Title, Author, Categories, Comments, Date
6. Publish

## Verification

After completing the installation and configuration:

1. **Test as Admin:** Log in as an administrator and verify screen options appear correctly
2. **Test as Other Roles:** Log in with different user roles to verify role-based settings
3. **Check Locked Settings:** If locked, verify users cannot modify the screen options
4. **Test Different Post Types:** Verify settings work across all configured post types

## Troubleshooting

### Plugin Not Activating

- Ensure your WordPress version is 6.8 or higher
- Verify PHP version is 8.1 or higher
- Check for plugin conflicts by deactivating other plugins

### Screen Options Not Applying

- Clear WordPress caches (object cache, page cache)
- Ensure the configuration is published (not in draft)
- Verify the post type/screen selection matches where you're testing

### Columns Not Showing

- Ensure the columns are registered by your theme or other plugins
- Check that column hooks are properly implemented
- Verify no other plugins are overriding screen options

### Getting Help

If you encounter issues during installation:

- **Issues & Bug Reports:** [GitHub Issues](https://github.com/rtCamp/advanced-screen-options/issues)
- **Feature Requests:** [GitHub Discussions](https://github.com/rtCamp/advanced-screen-options/discussions)
- **Documentation:** [Project Wiki](https://github.com/rtCamp/advanced-screen-options/wiki)

## Next Steps

Once installation is complete, refer to the [main README](../README.md) for:
- Usage instructions
- Plugin management features
- Advanced configuration options

---

**Need additional help?** Visit our [GitHub repository](https://github.com/rtCamp/advanced-screen-options) for the latest updates and community support.
